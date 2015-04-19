<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

/**
 * This class helps to find the available tables which should be dumped.
 */
class TableFinder
{

    /**
     * @var DumperConfigurationInterface
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AbstractPlatform
     */
    private $platform;


    /**
     * @param DumperConfigurationInterface $config
     * @param Connection                   $connection
     */
    public function __construct(DumperConfigurationInterface $config, Connection $connection)
    {
        $this->config = $config;
        $this->connection = $connection;
        $this->platform = $connection->getDatabasePlatform();
    }

    /**
     * Returns an array with all available tables, pre-sorted to handle all dependencies.
     *
     * As Doctrine DBAL doesn't quote the names of tables, columns and indexes, this function does the job.
     *
     * @return Table[]
     */
    public function findTables()
    {
        $sm = $this->connection->getSchemaManager();

        /** @var Table[] $tables */
        $tables = array();
        foreach ($sm->listTables() as $table) {
            $columns = array();
            foreach ($table->getColumns() as $column) {
                $columns[] = $this->createColumnReplacement($column);
            }

            $indexes = array();
            foreach ($table->getIndexes() as $index) {
                $indexes[] = $this->createIndexReplacement($index);
            }

            $foreignKeys = array();
            foreach ($table->getForeignKeys() as $fk) {
                $foreignKeys[] = $this->createForeignKeyReplacement($fk);
            }

            $tables[] = new Table(
                $this->platform->quoteIdentifier($table->getName()),
                $columns,
                $indexes,
                $foreignKeys,
                false,
                array()
            );
        }


        return $this->sortTables($tables);
    }

    /**
     * Brings the table into an order which fulfills all dependencies.
     *
     * @param Table[] $tables
     *
     * @return Table[]
     */
    private function sortTables(array $tables)
    {
        /** @var DependencyNode[] $dependencyNodes */
        $dependencyNodes = array();
        $dependencyGraph = new DependencyGraph();

        // create nodes for each table
        foreach ($tables as $table) {
            $node = new DependencyNode($table);

            $dependencyNodes[$table->getName()] = $node;
            $dependencyGraph->addNode($node);
        }

        // find and add dependencies
        foreach ($tables as $table) {
            $tableConfig = $this->config->getTable($table->getName());
            if ($tableConfig !== null) {
                foreach ($tableConfig->getDependencies() as $dependency) {
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
     * @param array $identifiers
     *
     * @return array
     */
    private function quoteIdentifiers(array $identifiers)
    {
        return array_map(array($this->platform, 'quoteIdentifier'), $identifiers);
    }

    /**
     * Creates a column replacement, which has a quoted name.
     *
     * @param Column $column
     *
     * @return Column
     */
    private function createColumnReplacement(Column $column)
    {
        $columnConfig = $column->toArray();
        $columnConfig['platformOptions'] = $column->getPlatformOptions();
        $columnConfig['customSchemaOptions'] = $column->getCustomSchemaOptions();

        return new Column(
            $this->platform->quoteIdentifier($column->getName()),
            $column->getType(),
            $columnConfig
        );
    }

    /**
     * Creates a index replacement, which has quoted names.
     *
     * @param Index $index
     *
     * @return Index
     */
    private function createIndexReplacement(Index $index)
    {
        return new Index(
            $this->platform->quoteIdentifier($index->getName()),
            $this->quoteIdentifiers($index->getColumns()),
            $index->isUnique(),
            $index->isPrimary(),
            $index->getFlags(),
            $index->getOptions()
        );
    }

    /**
     * Creates a foreign index replacement, which has quoted column names.
     *
     * @param ForeignKeyConstraint $fk
     *
     * @return ForeignKeyConstraint
     */
    private function createForeignKeyReplacement(ForeignKeyConstraint $fk)
    {
        return new ForeignKeyConstraint(
            $this->quoteIdentifiers($fk->getLocalColumns()),
            $this->platform->quoteIdentifier($fk->getForeignTableName()),
            $this->quoteIdentifiers($fk->getForeignColumns()),
            $this->platform->quoteIdentifier($fk->getName()),
            $fk->getOptions()
        );
    }
}
