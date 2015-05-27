<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Configuration\Table\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;

class DumperConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
{

    /**
     * @var DatabaseConfiguration
     */
    private $databaseConfiguration;

    /**
     * @var OutputConfiguration
     */
    private $outputConfiguration;

    /**
     * @var TableConfiguration[]
     */
    private $tableConfigurations = array();

    /**
     * @return DatabaseConfiguration
     */
    public function getDatabase()
    {
        return $this->databaseConfiguration;
    }

    /**
     * @return TableConfiguration[]
     */
    public function getTables()
    {
        return $this->tableConfigurations;
    }

    /**
     * @return array
     */
    public function getTableWhiteList()
    {
        return $this->get('table_white_list', array());
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setTableWhiteList(array $list)
    {
        return $this->set('table_white_list', $list);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTable($name)
    {
        return array_key_exists($name, $this->tableConfigurations);
    }

    /**
     * Returns the table for configuration of the table with the passed name.
     * If there is no configuration, null will be returned.
     *
     * @param string $name
     *
     * @return TableConfiguration
     */
    public function getTable($name)
    {
        if (!array_key_exists($name, $this->tableConfigurations)) {
            return null;
        }

        return $this->tableConfigurations[$name];
    }

    /**
     * Returns the table for configuration of the table with the passed name.
     * If there is no configuration, null will be returned.
     *
     * @param TableConfiguration $table
     *
     * @return TableConfiguration
     */
    public function addTable(TableConfiguration $table)
    {
        return $this->tableConfigurations[$table->getName()] = $table;
    }

    /**
     * @return string
     */
    public function getDumper()
    {
        return $this->get('dumper');
    }

    /**
     * @return string
     */
    public function getFullQualifiedDumperClassName()
    {
        return 'Digilist\\SnakeDumper\\Dumper\\' . $this->getDumper() . 'Dumper';
    }

    /**
     * @return OutputConfiguration
     */
    public function getOutput()
    {
        return $this->outputConfiguration;
    }

    protected function parseConfig(array $dumperConfig)
    {
        // ensure keys exist
        $dumperConfig = array_merge(array(
            'database' => array(),
            'output' => array(),
            'tables' => array(),
        ), $dumperConfig);

        $this->databaseConfiguration = new DatabaseConfiguration($dumperConfig['database']);
        $this->outputConfiguration = new OutputConfiguration($dumperConfig['output']);

        // parse tables
        foreach ($dumperConfig['tables'] as $name => $tableConfig) {
            $this->tableConfigurations[$name] = new TableConfiguration($name, $tableConfig);
        }

        // parse dependent tables and columns and match them
        // a table depends on another table if a dependend filter is defined
        foreach ($this->tableConfigurations as $table) {

            // if the dependent table is not configured, create a configuration
            foreach ($table->getDependencies() as $dependency) {
                if (!array_key_exists($dependency, $this->tableConfigurations)) {
                    $this->tableConfigurations[$dependency] = new TableConfiguration($dependency, array());
                }
            }

            // find dependent filters and add harvest columns
            foreach ($table->getFilters() as $filter) {
                if ($filter instanceof DataDependentFilter) {
                    // the dependent table needs to collect values of that column
                    $this->tableConfigurations[$filter->getReferencedTable()]->addHarvestColumn($filter->getReferencedColumn());
                }
            }
        }
    }
}
