<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\Table\Filter\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;

/**
 * This class helps to resolve table dependencies by foreign key constraints.
 */
class TableDependencyResolver
{

    /**
     * Add found dependencies into the table configs.
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
     * Brings the table into an order which respects dependencies between them.
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
                // TODO foreign keys pointing to own table not supported yet
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
     * Find dependencies on other tables in the given foreign keys and add those dependencies as filters to the
     * table configuration.
     *
     * @param ForeignKeyConstraint[]       $foreignKeys
     * @param TableConfiguration           $tableConfig
     * @param DumperConfigurationInterface $config
     *
     * @return bool
     */
    private function findDependencies(array $foreignKeys, TableConfiguration $tableConfig, DumperConfigurationInterface $config)
    {
        foreach ($foreignKeys as $foreignKey) {
            $referencedTable = $foreignKey->getForeignTableName();

            $referencedTableConfig = $config->getTableConfig($referencedTable);
            $hasDependency =
                $referencedTableConfig->getLimit() > 0
                || !empty($referencedTableConfig->getFilters())
                || $referencedTableConfig->getQuery() != null;

            if (!$hasDependency) {
                continue;
            }
            if ($foreignKey->getForeignTableName() === $tableConfig->getName()) {
                // TODO foreign keys pointing to own table not supported yet
                continue;
            }

            $tableConfig->addFilter(
                new DataDependentFilter(
                    $foreignKey->getColumns()[0],
                    $referencedTable,
                    $foreignKey->getForeignColumns()[0]
                )
            );
        }
    }
}
