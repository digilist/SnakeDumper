<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Converter\Service\SqlConverterService;
use Digilist\SnakeDumper\Dumper\Sql\DataSelector;
use Digilist\SnakeDumper\Dumper\Sql\TableSelector;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use PDO;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SqlDumper extends AbstractDumper
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    /**
     * @var array
     */
    private $collectedValues = array();

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $dumpOutput
     * @param InputInterface               $applicationInput
     * @param OutputInterface              $applicationOutput
     * @param Connection                   $connection
     */
    public function __construct(
        DumperConfigurationInterface $config,
        OutputInterface $dumpOutput,
        InputInterface $applicationInput,
        OutputInterface $applicationOutput,
        Connection $connection = null
    ) {
        parent::__construct($config, $dumpOutput, $applicationInput, $applicationOutput);
        $this->setConverterService(SqlConverterService::createFromConfig($config));

        if ($connection === null) {
            $connection = $this->connect();
        }

        $this->connection = $connection;
        $this->platform = $connection->getDatabasePlatform();
    }


    /**
     * {@inheritdoc}
     */
    public function dump()
    {
        $this->platform->registerDoctrineTypeMapping('enum', 'string');

        // Retrieve list of tables
        $tableFinder = new TableSelector($this->connection);
        $tables = $tableFinder->selectTables($this->config);

        $this->dumpPreamble();
        $this->dumpTableStructure($tables);
        $this->dumpTableContents($tables);
        $this->dumpConstraints($tables);
    }

    /**
     */
    private function dumpPreamble() {
        $this->dumpOutput->writeln($this->platform->getSqlCommentStartString() . ' ------------------------');
        $this->dumpOutput->writeln($this->platform->getSqlCommentStartString() . ' SnakeDumper SQL Dump');
        $this->dumpOutput->writeln($this->platform->getSqlCommentStartString() . ' ------------------------');
        $this->dumpOutput->writeln('');

        if ($this->platform instanceof MySqlPlatform) {
            $this->dumpOutput->writeln('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
        }

        $this->dumpOutput->writeln('');

        // TODO support other platforms
    }

    /**
     * Dumps the table structures.
     *
     * @param Table[] $tables
     */
    private function dumpTableStructure(array $tables)
    {
        $this->logger->info('Dumping table structure');
        foreach ($tables as $table) {
            $structure = $this->platform->getCreateTableSQL($table);

            $this->dumpOutput->writeln(implode(";\n", $structure) . ';');
        }
    }

    /**
     * @param Table[] $tables
     */
    private function dumpTableContents(array $tables) {
        $this->connection->setFetchMode(PDO::FETCH_ASSOC);
        $this->connection->getWrappedConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $stream = new NullOutput();
        if ($this->applicationInput->hasOption('progress') && $this->applicationInput->getOption('progress')) {
            if ($this->applicationOutput instanceof ConsoleOutput) {
                $stream = $this->applicationOutput->getErrorOutput();
            }
        }

        $progress = new ProgressBar($stream, count($tables));
        // add an additional space, in case logging is also enabled
        $progress->setFormat($progress->getFormatDefinition('normal') . ' ');
        $progress->start();

        foreach ($tables as $table) {
            $progress->advance();

            $tableConfig = $this->config->getTableConfig($table->getName());

            $this->initValueHarvesting($tableConfig);

            // check if table contents should be ignored
            if ($tableConfig->isContentIgnored()) {
                $this->logger->info('Ignoring contents of table ' . $table->getName());
                continue;
            }

            $this->logger->info('Dumping table ' . $table->getName());
            $this->dumpTableContent($tableConfig, $table);
        }

        $progress->finish();
        $this->logger->info('Dump finished');
    }

    /**
     * Dumps the contents of a single table. The TableConfig is optional as tables do not need to be configured.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     */
    private function dumpTableContent(TableConfiguration $tableConfig, Table $table) {
        // Ensure connection is still open
        $this->reconnectIfNecessary();

        $pdo = $this->connection->getWrappedConnection();

        $tableName = $table->getName();
        $quotedTableName = $table->getQuotedName($this->platform);
        $insertColumns = null;

        $collectColumns = $tableConfig->getHarvestColumns();
        $bufferSize = $this->config->getOutputConfig()->getRowsPerStatement();;
        $bufferCount = 0; // number of rows in buffer
        $buffer = array(); // array to buffer rows

        $dataSelector = new DataSelector($this->connection);
        $result = $dataSelector->executeSelectQuery($tableConfig, $table, $this->collectedValues);
        foreach ($result as $row) {
            /*
             * The following code (in this loop) will be executed for each dumped row!
             * Performance in this place is essential!
             */

            foreach ($collectColumns as $collectColumn) {
                if (!isset($row[$collectColumn])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Trying to collect value of column %s in table %s which does not exist.',
                            $collectColumn,
                            $tableName
                        )
                    );
                }
                $this->collectedValues[$tableName][$collectColumn][] = $row[$collectColumn];
            }

            $context = $row;
            foreach ($row as $key => $value) {
                $value = $this->convert($tableName . '.' . $key, $value, $context);

                if (is_null($value)) {
                    $value = 'NULL';
                } else {
                    $value = $pdo->quote($value);
                }

                $row[$key] = $value;
            }

            if ($insertColumns === null) {
                // the insert Columns depend on the result set
                // as custom queries are possible it can't be predefined
                $insertColumns = $this->extractInsertColumns(array_keys($row));
            }

            $buffer[] = '(' . implode(', ', $row) . ')';
            $bufferCount++;

            if ($bufferCount >= $bufferSize) {
                $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                    ' VALUES ' . implode(', ', $buffer) . ';';

                $this->dumpOutput->writeln($query);

                $buffer = array();
                $bufferCount = 0;
            }
        }

        if ($bufferCount > 0) {
            $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                ' VALUES ' . implode(', ', $buffer) . ';';

            $this->dumpOutput->writeln($query);
        }
    }

    /**
     * Dump the constraints / foreign keys of all tables.
     *
     * @param Table[]            $tables
     */
    private function dumpConstraints(array $tables) {
        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $constraint) {
                $constraint = $this->platform->getCreateConstraintSQL($constraint, $table);

                $this->dumpOutput->writeln($constraint . ';');
            }
        }
    }

    /**
     * Get a SQL-formatted string of the column order to be used in the INSERT statement.
     * (INSERT INTO foo (--> column1, column2, column3, ... <--)
     *
     * @param array $columns
     *
     * @return string
     */
    private function extractInsertColumns($columns)
    {
        $columns = array_map(function ($column) {
            return $this->platform->quoteIdentifier($column);
        }, $columns);

        return implode(', ', $columns);
    }

    /**
     * Init the array that is used to harvest values for dependency resolving.
     *
     * @param TableConfiguration $tableConfig
     */
    private function initValueHarvesting(TableConfiguration $tableConfig)
    {
        $this->collectedValues[$tableConfig->getName()] = array();
        $collectColumns = $tableConfig->getHarvestColumns();
        foreach ($collectColumns as $collectColumn) {
            $this->collectedValues[$tableConfig->getName()][$collectColumn] = array();
        }
    }

    /**
     * Connect to the database.
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return Connection
     */
    private function connect()
    {
        $connectionParams = array(
            'driver'   => $this->config->getDatabaseConfig()->getDriver(),
            'host'     => $this->config->getDatabaseConfig()->getHost(),
            'user'     => $this->config->getDatabaseConfig()->getUser(),
            'password' => $this->config->getDatabaseConfig()->getPassword(),
            'dbname'   => $this->config->getDatabaseConfig()->getDatabaseName(),
            'charset'  => $this->config->getDatabaseConfig()->getCharset(),
        );

        $dbalConfig = new Configuration();
        $connection = DriverManager::getConnection($connectionParams, $dbalConfig);
        $connection->connect();

        return $connection;
    }

    /**
     * This method checks whether the connection is still open or reconnects otherwise.
     *
     * The connection might drop in some scenarios, where the server has a configured timeout and the handling
     * of the result set takes longer. To prevent failures of the dumper, the connection will be opened again.
     */
    private function reconnectIfNecessary()
    {
        if (!$this->connection->ping()) {
            $this->connection->close();
            $this->connect()->connect();
        }
    }
}
