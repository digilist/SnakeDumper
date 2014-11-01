<?php

namespace Digilist\SnakeDumper\Exporter;

use Digilist\SnakeDumper\Logger\PsrSQLLogger;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Statement;
use PDO;
use Symfony\Component\Console\Logger\ConsoleLogger;

class SqlDumper implements DumperInterface
{

    public function dump(array $config)
    {
//        $logger = new ConsoleLogger($output);

        $dbalConfig = new Configuration();
//        $dbalConfig->setSQLLogger(new PsrSQLLogger($logger));

        $connectionParams = array(
            'driver' => $config['database']['driver'],
            'host' => $config['database']['host'],
            'user' => $config['database']['user'],
            'password' => $config['database']['password'],
            'dbname' => $config['database']['dbname'],
        );
        $conn = DriverManager::getConnection($connectionParams, $dbalConfig);
        $conn->connect();

        $platform = $conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $sm = $conn->getSchemaManager();
        $tables = $sm->listTables();

        $this->getTableSchemaSql($tables, $conn->getDatabasePlatform());
        $this->dumpTables($tables, $conn);
    }

    /**
     * @param Table[]          $tables
     * @param AbstractPlatform $platform
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getTableSchemaSql(array $tables, AbstractPlatform $platform)
    {
        $schema = [
            'tables' => [],
            'constraints' => [],
        ];

        foreach ($tables as $table) {
            $schema['tables'][] = $platform->getCreateTableSQL($table); // CREATE TABLE + indexes
            foreach ($table->getForeignKeys() as $constraint) {
                $schema['constraints'][] = $platform->getCreateConstraintSQL($constraint, $table);
            }
        }

        $schema['tables'] = array_map([$this, 'implodeQueries'], $schema['tables']);
        $schema['tables'] = $this->implodeQueries($schema['tables']);
        $schema['constraints'] = $this->implodeQueries($schema['constraints']);

        $schema = $this->implodeQueries($schema);

        echo $schema . ';';
    }

    private function implodeQueries($queries)
    {
        return implode(";\n", $queries);
    }

    /**
     * @param Table[]    $tables
     * @param Connection $conn
     */
    private function dumpTables(array $tables, Connection $conn)
    {
        $conn->setFetchMode(PDO::FETCH_NUM);

        $platform = $conn->getDatabasePlatform();
        $pdo = $conn->getWrappedConnection();

        foreach ($tables as $table) {
            $qb = $conn->createQueryBuilder();

            $tableName = $table->getQuotedName($platform);
            $columns = array_map(
                function (Column $column) use ($platform) {
                    return $column->getQuotedName($platform);
                },
                $table->getColumns()
            );
            $columns = implode(', ', $columns);

            /** @var Statement $result */
            $result = $qb->select('*')->from($table->getName(), 't')->execute();

            foreach ($result as $row) {

                foreach ($row as $key => $val) {
                    if (is_null($val))
                        $row[$key] = 'NULL';
                    else if (ctype_digit($val))
                        $row[$key] = $val;
                    else {
                        $row[$key] = $pdo->quote($val);
                    }
                }

                $query = 'INSERT INTO ' . $tableName . ' (' . $columns . ')' .
                    ' VALUES (' . implode(', ', $row) . ');' . PHP_EOL;

                echo $query;
            }
        }
    }
}
