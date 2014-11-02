<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\TableConfiguration;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Statement;
use PDO;
use Symfony\Component\Console\Output\OutputInterface;

class SqlDumper extends AbstractDumper
{

    /**
     * {@inheritdoc}
     */
    public function dump(DumperConfigurationInterface $config, OutputInterface $output)
    {
        $dbalConfig = new Configuration();

        $connectionParams = array(
            'driver' => $config->getDatabase()->getDriver(),
            'host' => $config->getDatabase()->getHost(),
            'user' => $config->getDatabase()->getUser(),
            'password' => $config->getDatabase()->getPassword(),
            'dbname' => $config->getDatabase()->getDatabaseName(),
            'charset' => $config->getDatabase()->getCharset(),
        );
        $conn = DriverManager::getConnection($connectionParams, $dbalConfig);
        $conn->connect();

        $platform = $conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $sm = $conn->getSchemaManager();
        $tables = $sm->listTables();

        $this->dumpTableSchema($config, $tables, $conn->getDatabasePlatform(), $output);
        $this->dumpTables($config, $tables, $conn, $output);
    }

    /**
     * @param DumperConfigurationInterface $config
     * @param Table[]                      $tables
     * @param AbstractPlatform             $platform
     * @param OutputInterface              $output
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function dumpTableSchema(
        DumperConfigurationInterface $config,
        array $tables,
        AbstractPlatform $platform,
        OutputInterface $output
    ) {
        $schema = [
            'tables' => [],
            'constraints' => [],
        ];

        foreach ($tables as $table) {
            if ($config->hasTable($table->getName()) && $config->getTable($table->getName())->isTableIgnored()) {
                continue;
            }

            $schema['tables'][] = $platform->getCreateTableSQL($table); // CREATE TABLE + indexes
            foreach ($table->getForeignKeys() as $constraint) {
                $schema['constraints'][] = $platform->getCreateConstraintSQL($constraint, $table);
            }
        }

        $schema['tables'] = array_map([$this, 'implodeQueries'], $schema['tables']);
        $schema['tables'] = $this->implodeQueries($schema['tables']);
        $schema['constraints'] = $this->implodeQueries($schema['constraints']);

        $schema = $this->implodeQueries($schema);

        $output->writeln($schema . ';');
    }

    /**
     * @param DumperConfigurationInterface $config
     * @param Table[]                      $tables
     * @param Connection                   $conn
     * @param OutputInterface              $output
     */
    private function dumpTables(
        DumperConfigurationInterface $config,
        array $tables,
        Connection $conn,
        OutputInterface $output
    ) {
        $conn->setFetchMode(PDO::FETCH_ASSOC);
        $conn->getWrappedConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        foreach ($tables as $table) {
            $tableConfig = $config->getTable($table->getName());

            // check if table contents are ignored
            if ($tableConfig != null) {
                if ($tableConfig->isContentIgnored() || $tableConfig->isTableIgnored()) {
                    continue;
                }
            }

            $this->dumpTable($tableConfig, $table, $conn, $output);
        }
    }

    /**
     * Dumps the contents of a single table.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param Connection         $conn
     * @param OutputInterface    $output
     */
    private function dumpTable(
        TableConfiguration $tableConfig = null,
        Table $table,
        Connection $conn,
        OutputInterface $output
    ) {
        $platform = $conn->getDatabasePlatform();
        $pdo = $conn->getWrappedConnection();

        $tableName = $table->getQuotedName($platform);
        $columns = $this->getColumnsForInsertStatement($table, $platform);

        /** @var Statement $result */
        if ($tableConfig != null && $tableConfig->getQuery() != null) {
            $result = $conn->prepare($tableConfig->getQuery());
            $result->execute();
        } else {
            $qb = $conn->createQueryBuilder()
                ->select('*')
                ->from($table->getName(), 't');

            if ($tableConfig != null) {
                if ($tableConfig->getLimit() != null) {
                    $qb->setMaxResults($tableConfig->getLimit());
                }
                if ($tableConfig->getOrderBy() != null) {
                    $qb->add('orderBy', $tableConfig->getOrderBy());
                }
            }

            $result = $qb->execute();
        }

        foreach ($result as $row) {
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

            $query = 'INSERT INTO ' . $tableName . ' (' . $columns . ')' .
                ' VALUES (' . implode(', ', $row) . ');';

            $output->writeln($query);
        }
    }

    /**
     * Returns the columns of a table to use it in the insert statement.
     *
     * @param Table            $table
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    private function getColumnsForInsertStatement(Table $table, AbstractPlatform $platform)
    {
        $columns = array_map(
            function (Column $column) use ($platform) {
                return $column->getQuotedName($platform);
            },
            $table->getColumns()
        );
        return implode(', ', $columns);
    }

    /**
     * Combines multiple queries.
     *
     * @param $queries
     *
     * @return string
     */
    private function implodeQueries($queries)
    {
        return implode(";\n", $queries);
    }
}
