<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\Table\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;

/**
 * This class helps to resolve table dependencies by foreign key constraints.
 */
class TableDependencyResolver
{

    /**
     * Supplement table configs with dependencies that were resolved from the database.
     *
     * @param Table[] $tables
     * @param DumperConfigurationInterface $config
     */
    public function createDependentFilters(array $tables, DumperConfigurationInterface $config)
    {
        foreach ($tables as $table) {
            $tableConfig = $config->getTableConfig($table->getName());
            $this->findDependencies($table->getForeignKeys(), $tableConfig, $config);
        }

        $config->parseDependencies();
    }

    /**
     * Brings the table into an order which respects dependencies betweem them.
     *
     * @param Table[] $tables
     *
     * @return Table[]
     */
    public function sortTablesByDependencies(array $tables)
    {
        $nodes = array();
        $dependencyGraph = new DependencyGraph();

        // create nodes for each table
        foreach ($tables as $table) {
            $node = new DependencyNode($table, $table->getName());

            $nodes[$table->getName()] = $node;
            $dependencyGraph->addNode($node);
        }

        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                // TODO
                if ($foreignKey->getForeignTableName() == $table->getName()) {
                    continue;
                }

                $dependencyGraph->addDependency(
                    $nodes[$table->getName()],
                    $nodes[$foreignKey->getForeignTableName()]
                );
            }
        }

        // TODO why the filter?
        return array_filter($dependencyGraph->resolve());
    }

    /**
     * @param ForeignKeyConstraint[]       $foreignKeys
     * @param TableConfiguration           $tableConfig
     * @param DumperConfigurationInterface $config
     *
     * @return bool
     */
    private function findDependencies(array $foreignKeys, TableConfiguration $tableConfig, DumperConfigurationInterface $config)
    {
        foreach ($foreignKeys as $foreignKey) {
            $foreignTable = $foreignKey->getForeignTableName();

            if (!$config->hasTableConfig($foreignTable)) {
                $config->addTableConfig(new TableConfiguration($foreignTable));
            }

            $foreignTableConfig = $config->getTableConfig($foreignTable);
            if ($foreignTableConfig->getLimit() > 0 || count($foreignTableConfig->getFilters()) > 0 ||
                    $foreignTableConfig->getQuery() != null) {
                $tableConfig->addFilter(new DataDependentFilter(array(
                    'columnName' => $foreignKey->getColumns()[0],
                    'referencedTable' => $foreignTable,
                    'referencedColumn' => $foreignKey->getForeignColumns()[0],
                )));
            }
        }
    }
}
