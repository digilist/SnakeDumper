<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
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

    public function dump(DumperConfigurationInterface $config, OutputInterface $output)
    {
        $dbalConfig = new Configuration();

//        $logger = new ConsoleLogger($output);
//        $dbalConfig->setSQLLogger(new PsrSQLLogger($logger));

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

        $this->dumpTableSchema($tables, $conn->getDatabasePlatform(), $output);
        $this->dumpTables($tables, $conn, $output);
    }

    /**
     * @param Table[]          $tables
     * @param AbstractPlatform $platform
     * @param OutputInterface  $output
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function dumpTableSchema(array $tables, AbstractPlatform $platform, OutputInterface $output)
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

        $output->writeln($schema . ';');
    }

    /**
     * @param Table[]         $tables
     * @param Connection      $conn
     * @param OutputInterface $output
     */
    private function dumpTables(array $tables, Connection $conn, OutputInterface $output)
    {
        $conn->setFetchMode(PDO::FETCH_ASSOC);

        $platform = $conn->getDatabasePlatform();
        $pdo = $conn->getWrappedConnection();

        foreach ($tables as $table) {
            $qb = $conn->createQueryBuilder();

            $tableName = $table->getQuotedName($platform);
            $columnNames = array_values(array_map(
                function (Column $column) use ($platform) {
                    return $column->getName();
                },
                $table->getColumns()
            ));
            $columns = array_map(
                function (Column $column) use ($platform) {
                    return $column->getQuotedName($platform);
                },
                $table->getColumns()
            );
            $columns = implode(', ', $columns);

            /** @var Statement $result */
            $result = $qb->select('*')
                ->from($table->getName(), 't')
                ->execute();

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
    }

    private function implodeQueries($queries)
    {
        return implode(";\n", $queries);
    }
}
