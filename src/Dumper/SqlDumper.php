<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\Table\DataDependentFilterConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\TableFilter;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
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
     * {@inheritdoc}
     */
    public function dump(DumperConfigurationInterface $config, OutputInterface $output)
    {
        $connectionParams = array(
            'driver' => $config->getDatabase()->getDriver(),
            'host' => $config->getDatabase()->getHost(),
            'user' => $config->getDatabase()->getUser(),
            'password' => $config->getDatabase()->getPassword(),
            'dbname' => $config->getDatabase()->getDatabaseName(),
            'charset' => $config->getDatabase()->getCharset(),
        );

        $dbalConfig = new Configuration();
        $conn = DriverManager::getConnection($connectionParams, $dbalConfig);
        $conn->connect();

        $platform = $conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $this->collectedValues = array();

        // Retrieve list of tables
        $tables = $this->getTables($config, $conn, $platform);

        $filter = new TableFilter($config);
        $tables = $filter->filterWhiteListTables($tables);
        $tables = $filter->filterIgnoredTables($tables);

        $this->dumpPreamble($config, $platform, $output);
        $this->dumpTableStructure($tables, $platform, $output);
        $this->dumpTableContents($config, $tables, $conn, $output);
        $this->dumpConstraints($config, $tables, $platform, $output);
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
            $output->writeln('SET FOREIGN_KEY_CHECKS=0');
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
     * @param Connection                   $conn
     * @param OutputInterface              $output
     */
    private function dumpTableContents(
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
            if (null !== $tableConfig && $tableConfig->isContentIgnored()) {
                continue;
            }

            $this->dumpTableContent($tableConfig, $table, $conn, $output);
        }
    }

    /**
     * Dumps the contents of a single table. The TableConfig is optional as tables do not need to be configured.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param Connection         $conn
     * @param OutputInterface    $output
     */
    private function dumpTableContent(
        TableConfiguration $tableConfig = null,
        Table $table,
        Connection $conn,
        OutputInterface $output
    ) {
        $platform = $conn->getDatabasePlatform();
        $pdo = $conn->getWrappedConnection();

        $tableName = $table->getName();
        $quotedTableName = $table->getQuotedName($platform);
        $insertColumns = null;

        $this->collectedValues[$tableName] = array();
        $collectColumns = array();
        if ($tableConfig !== null) {
            $collectColumns = $tableConfig->getCollectColumns();
            foreach ($collectColumns as $collectColumn) {
                $this->collectedValues[$tableName][$collectColumn] = array();
            }
        }

        $result = $this->executeSelectQuery($tableConfig, $table, $conn);
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

            if ($insertColumns === null) {
                // the insert Columns depend on the result set
                // as custom queries are possible it can't be predefined
                $insertColumns = $this->extractInsertColumns($platform, array_keys($row));
            }

            foreach ($collectColumns as $collectColumn) {
                $this->collectedValues[$tableName][$collectColumn][] = $row[$collectColumn];
            }

            $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                ' VALUES (' . implode(', ', $row) . ');';

            $output->writeln($query);
        }
    }

    /**
     * Dump the constraints / foreign keys of all tables.
     *
     * @param DumperConfigurationInterface $config
     * @param Table[]            $tables
     * @param AbstractPlatform   $platform
     * @param OutputInterface    $output
     */
    private function dumpConstraints(
        DumperConfigurationInterface $config,
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

    /**
     * Returns an array with all available tables, pre-sorted to handle all dependencies.
     *
     * As Doctrine DBAL doesn't quote the names of tables, columns and indexes, this function does the job.
     *
     * @param DumperConfigurationInterface $config
     * @param Connection $conn
     * @param AbstractPlatform $platform
     * @return \Doctrine\DBAL\Schema\Table[]
     */
    private function getTables(DumperConfigurationInterface $config, Connection $conn, AbstractPlatform $platform)
    {
        $sm = $conn->getSchemaManager();

        /** @var Table[] $tables */
        $tables = array();
        foreach ($sm->listTables() as $table) {
            $columns = array();
            foreach ($table->getColumns() as $column) {
                $columnConfig = $column->toArray();
                $columnConfig['platformOptions'] = $column->getPlatformOptions();
                $columnConfig['customSchemaOptions'] = $column->getCustomSchemaOptions();

                $columns[] = new Column(
                    $platform->quoteIdentifier($column->getName()),
                    $column->getType(),
                    $columnConfig
                );
            }

            $indexes = array();
            foreach ($table->getIndexes() as $index) {
                $indexes[] = new Index(
                    $platform->quoteIdentifier($index->getName()),
                    $this->quoteIdentifiers($platform, $index->getColumns()),
                    $index->isUnique(),
                    $index->isPrimary(),
                    $index->getFlags(),
                    $index->getOptions()
                );
            }

            $foreignKeys = array();
            foreach ($table->getForeignKeys() as $fk) {
                $foreignKeys[] = new ForeignKeyConstraint(
                    $this->quoteIdentifiers($platform, $fk->getLocalColumns()),
                    $platform->quoteIdentifier($fk->getForeignTableName()),
                    $this->quoteIdentifiers($platform, $fk->getForeignColumns()),
                    $platform->quoteIdentifier($fk->getName()),
                    $fk->getOptions()
                );
            }

            $tables[] = new Table(
                $platform->quoteIdentifier($table->getName()),
                $columns,
                $indexes,
                $foreignKeys,
                false,
                array()
            );
        }

        // Sort tables so that all dependencies can be fulfilled.
        /** @var DependencyNode[] $dependencyNodes */
        $dependencyNodes = array();
        $dependencyGraph = new DependencyGraph();

        foreach ($tables as $table) {
            if (!isset($dependencyNodes[$table->getName()])) {
                $dependencyNodes[$table->getName()] = new DependencyNode();
            }

            $dependencyNodes[$table->getName()]->setElement($table);
            $dependencyGraph->addNode($dependencyNodes[$table->getName()]);

            // find and add dependencies
            $tableConfig = $config->getTable($table->getName());
            if ($tableConfig !== null) {
                foreach ($tableConfig->getDependencies() as $dependency) {
                    if (!isset($dependencyNodes[$dependency])) {
                        $dependencyNodes[$dependency] = new DependencyNode(null);
                    }

                    $dependencyGraph->addDependency($dependencyNodes[$table->getName()], $dependencyNodes[$dependency]);
                }
            }
        }

        $tables = array_filter($dependencyGraph->resolve());

        return $tables;
    }

    /**
     * Quote multiple identifiers.
     *
     * @param AbstractPlatform $platform
     * @param array            $identifiers
     *
     * @return array
     */
    private function quoteIdentifiers(AbstractPlatform $platform, array $identifiers)
    {
        return array_map(array($platform, 'quoteIdentifier'), $identifiers);
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param Connection         $conn
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\DBAL\DBALException
     */
    private function executeSelectQuery(TableConfiguration $tableConfig = null, Table $table, Connection $conn)
    {
        if ($tableConfig != null && $tableConfig->getQuery() != null) {
            $result = $conn->prepare($tableConfig->getQuery());
            $result->execute();

            return $result;
        }

        $qb = $conn->createQueryBuilder()
            ->select('*')
            ->from($table->getName(), 't');

        if ($tableConfig != null) {
            $this->addFiltersToSelectQuery($qb, $tableConfig);

            if ($tableConfig->getLimit() != null) {
                $qb->setMaxResults($tableConfig->getLimit());
            }
            if ($tableConfig->getOrderBy() != null) {
                $qb->add('orderBy', $tableConfig->getOrderBy());
            }
        }

        $result = $qb->execute();

        return $result;
    }

    /**
     * Add the configured filter to the select query.
     *
     * @param QueryBuilder $qb
     * @param TableConfiguration $tableConfig
     */
    private function addFiltersToSelectQuery(QueryBuilder $qb, TableConfiguration $tableConfig)
    {
        foreach ($tableConfig->getColumns() as $column) {
            $filters = $column->getFilters();

            foreach ($filters as $index => $filter) {
                if ($filter instanceof DataDependentFilterConfiguration) {
                    if (!isset($this->collectedValues[$filter->getTable()])) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The table %s has not been dumped before %s',
                                $filter->getTable(),
                                $tableConfig->getName()
                            )
                        );
                    }
                    if (!isset($this->collectedValues[$filter->getTable()][$filter->getColumn()])) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The column %s on table %s has not been dumped.',
                                $filter->getTable(),
                                $tableConfig->getName()
                            )
                        );
                    }

                    $filter->setValue($this->collectedValues[$filter->getTable()][$filter->getColumn()]);
                }

                if ($filter->getOperator() === 'in' || $filter->getOperator() === 'notIn') {
                    // the in and notIn operator expects an array which needs different handling

                    $param = array();
                    foreach ((array) $filter->getValue() as $valueIndex => $value) {
                        $tmpParam = 'param_' . $index . '_' . $valueIndex;
                        $param[] = ':' . $tmpParam;

                        $qb->setParameter($tmpParam, $value);
                    }
                } else {
                    $param = ':param_' . $index;

                    $qb->setParameter('param_' . $index, $filter->getValue());
                }

                $expr = call_user_func_array(array($qb->expr(), $filter->getOperator()), array(
                    $column->getName(),
                    $param
                ));
                $qb->andWhere($expr);
            }
        }
    }
}
