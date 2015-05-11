<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\Table\DataDependentFilterConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\DataSelector;
use Digilist\SnakeDumper\Dumper\Sql\TableFilter;
use Digilist\SnakeDumper\Dumper\Sql\TableFinder;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;
use PDO;
use Symfony\Component\Console\Output\OutputInterface;

class SqlDumper extends AbstractDumper
{

    /**
     * @var array
     */
    private $collectedValues;

    /**
     * @var DataSelector
     */
    private $dataSelector;

    /**
     * {@inheritdoc}
     */
    public function dump(DumperConfigurationInterface $config, OutputInterface $output, Connection $connection = null)
    {
        if ($connection === null) {
            $connectionParams = array(
                'driver'   => $config->getDatabase()->getDriver(),
                'host'     => $config->getDatabase()->getHost(),
                'user'     => $config->getDatabase()->getUser(),
                'password' => $config->getDatabase()->getPassword(),
                'dbname'   => $config->getDatabase()->getDatabaseName(),
                'charset'  => $config->getDatabase()->getCharset(),
            );

            $dbalConfig = new Configuration();
            $connection = DriverManager::getConnection($connectionParams, $dbalConfig);
            $connection->connect();
        }

        $platform = $connection->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $this->dataSelector = new DataSelector($connection);
        $this->collectedValues = array();

        // Retrieve list of tables
        $tableFinder = new TableFinder($config, $connection);
        $tables = $tableFinder->findTables();

        $filter = new TableFilter($config);
        $tables = $filter->filterWhiteListTables($tables);
        $tables = $filter->filterIgnoredTables($tables);

        $this->dumpPreamble($config, $platform, $output);
        $this->dumpTableStructure($tables, $platform, $output);
        $this->dumpTableContents($config, $tables, $connection, $output, $config->getOutput()->getRowsPerStatement());
        $this->dumpConstraints($tables, $platform, $output);
    }

    /**
     * @param DumperConfigurationInterface $config
     * @param AbstractPlatform             $platform
     * @param OutputInterface              $output
     */
    private function dumpPreamble(
        DumperConfigurationInterface $config,
        AbstractPlatform $platform,
        OutputInterface $output
    ) {

        $output->writeln($platform->getSqlCommentStartString() . ' ------------------------');
        $output->writeln($platform->getSqlCommentStartString() . ' SnakeDumper SQL Dump');
        $output->writeln($platform->getSqlCommentStartString() . ' ------------------------');
        $output->writeln('');

        if ($platform instanceof MySqlPlatform) {
            $output->writeln('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
        }

        $output->writeln('');

        // TODO support other platforms
    }

    /**
     * Dumps the table structures.
     *
     * @param Table[] $tables
     * @param AbstractPlatform $platform
     * @param OutputInterface $output
     */
    private function dumpTableStructure(array $tables, AbstractPlatform $platform, OutputInterface $output)
    {
        foreach ($tables as $table) {
            $structure = $platform->getCreateTableSQL($table);

            $output->writeln(implode(";\n", $structure) . ';');
        }
    }

    /**
     * @param DumperConfigurationInterface $config
     * @param Table[]                      $tables
     * @param Connection                   $connection
     * @param OutputInterface              $output
     * @param int                          $rowsPerStatement
     */
    private function dumpTableContents(
        DumperConfigurationInterface $config,
        array $tables,
        Connection $connection,
        OutputInterface $output,
        $rowsPerStatement
    ) {
        $connection->setFetchMode(PDO::FETCH_ASSOC);
        $connection->getWrappedConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        foreach ($tables as $table) {
            $tableConfig = $config->getTable($table->getName());

            // check if table contents should be ignored
            if (null !== $tableConfig && $tableConfig->isContentIgnored()) {
                continue;
            }

            $this->dumpTableContent($tableConfig, $table, $connection, $output, $rowsPerStatement);
        }
    }

    /**
     * Dumps the contents of a single table. The TableConfig is optional as tables do not need to be configured.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param Connection         $connection
     * @param OutputInterface    $output
     * @param int                $bufferSize
     */
    private function dumpTableContent(
        TableConfiguration $tableConfig = null,
        Table $table,
        Connection $connection,
        OutputInterface $output,
        $bufferSize
    ) {
        $platform = $connection->getDatabasePlatform();
        $pdo = $connection->getWrappedConnection();

        $tableName = $table->getName();
        $quotedTableName = $table->getQuotedName($platform);
        $insertColumns = null;

        $bufferRowCount = 0; // number of rows in buffer
        $buffer = array(); // array to buffer rows

        $this->collectedValues[$tableName] = array();
        $collectColumns = array();
        if ($tableConfig !== null) {
            $collectColumns = $tableConfig->getHarvestColumns();
            foreach ($collectColumns as $collectColumn) {
                $this->collectedValues[$tableName][$collectColumn] = array();
            }
        }

        $result = $this->dataSelector->executeSelectQuery($tableConfig, $table, $this->collectedValues);
        foreach ($result as $row) {
            /*
             * The following code (in this loop) will be executed for each dumped row!
             * Performance in this place is essential!
             */

            $context = $row;
            foreach ($row as $key => $value) {
                $value = $this->convert($tableName . '.' . $key, $value, $context);

                if (is_null($value)) {
                    $value = 'NULL';
                } elseif (!ctype_digit($value)) {
                    $value = $pdo->quote($value);
                }

                $row[$key] = $value;
            }

            if ($insertColumns === null) {
                // the insert Columns depend on the result set
                // as custom queries are possible it can't be predefined
                $insertColumns = $this->extractInsertColumns($platform, array_keys($row));
            }

            foreach ($collectColumns as $collectColumn) {
                if (!isset($row[$collectColumn])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Trying to collect value of column %s in table %s which does not exist.',
                            $collectColumn,
                            $tableName
                        )
                    );
                }
                $this->collectedValues[$tableName][$collectColumn][] = $row[$collectColumn];
            }

            $buffer[] = '(' . implode(', ', $row) . ')';
            $bufferRowCount++;

            if ($bufferRowCount >= $bufferSize) {
                $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                    ' VALUES ' . implode(', ', $buffer) . ';';

                $output->writeln($query);

                $buffer = array();
                $bufferRowCount = 0;
            }
        }

        if ($bufferRowCount > 0) {
            $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                ' VALUES ' . implode(', ', $buffer) . ';';

            $output->writeln($query);
        }
    }

    /**
     * Dump the constraints / foreign keys of all tables.
     *
     * @param Table[]            $tables
     * @param AbstractPlatform   $platform
     * @param OutputInterface    $output
     */
    private function dumpConstraints(
        array $tables,
        AbstractPlatform $platform,
        OutputInterface $output
    ) {
        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $constraint) {
                $constraint = $platform->getCreateConstraintSQL($constraint, $table);

                $output->writeln($constraint . ';');
            }
        }
    }

    /**
     * Get a SQL-formatted string of the column order to be used in the INSERT statement.
     * (INSERT INTO foo (--> column1, column2, column3, ... <--)
     *
     * @param AbstractPlatform $platform
     * @param array            $columns
     *
     * @return string
     */
    private function extractInsertColumns(AbstractPlatform $platform, $columns)
    {
        $columns = array_map(function ($column) use ($platform) {
            return $platform->quoteIdentifier($column);
        }, $columns);

        return implode(', ', $columns);
    }
}
