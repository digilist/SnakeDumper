<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Configuration\Table\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;

class DumperConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
{

    /**
     * @var DatabaseConfiguration
     */
    private $databaseConfig;

    /**
     * @var OutputConfiguration
     */
    private $outputConfig;

    /**
     * @var TableConfiguration[]
     */
    private $tableConfigs = array();

    /**
     * @return DatabaseConfiguration
     */
    public function getDatabaseConfig()
    {
        return $this->databaseConfig;
    }

    /**
     * @return TableConfiguration[]
     */
    public function getTableConfigs()
    {
        return $this->tableConfigs;
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
    public function hasTableConfig($name)
    {
        return array_key_exists($name, $this->tableConfigs);
    }

    /**
     * Returns the table for configuration of the table with the passed name.
     * If there is no configuration, null will be returned.
     *
     * @param string $name
     *
     * @return TableConfiguration
     */
    public function getTableConfig($name)
    {
        if (!array_key_exists($name, $this->tableConfigs)) {
            return null;
        }

        return $this->tableConfigs[$name];
    }

    /**
     * Add a new table configuration.
     *
     * @param TableConfiguration $tableConfig
     *
     * @return TableConfiguration
     */
    public function addTableConfig(TableConfiguration $tableConfig)
    {
        return $this->tableConfigs[$tableConfig->getName()] = $tableConfig;
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
    public function getOutputConfig()
    {
        return $this->outputConfig;
    }

    /**
     * Parse table dependencies and add the appropriate harvest columns, if a dependent file exists.
     */
    public function parseDependencies()
    {
        // parse dependent tables and columns and match them
        // a table depends on another table if a dependent filter is defined
        foreach ($this->tableConfigs as $table) {
            // if the dependent table is not configured, create a configuration
            foreach ($table->getDependencies() as $dependency) {
                if (!array_key_exists($dependency, $this->tableConfigs)) {
                    $this->tableConfigs[$dependency] = new TableConfiguration($dependency, array());
                }
            }

            // find dependent filters and add harvest columns
            foreach ($table->getFilters() as $filter) {
                if ($filter instanceof DataDependentFilter) {
                    // the dependent table needs to collect values of that column
                    $this->tableConfigs[$filter->getReferencedTable()]->addHarvestColumn($filter->getReferencedColumn());
                }
            }
        }
    }

    protected function parseConfig(array $dumperConfig)
    {
        // ensure keys exist
        $dumperConfig = array_merge(array(
            'database' => array(),
            'output' => array(),
            'tables' => array(),
        ), $dumperConfig);

        $this->databaseConfig = new DatabaseConfiguration($dumperConfig['database']);
        $this->outputConfig = new OutputConfiguration($dumperConfig['output']);

        // parse tables
        foreach ($dumperConfig['tables'] as $name => $tableConfig) {
            $this->tableConfigs[$name] = new TableConfiguration($name, $tableConfig);
        }

        $this->parseDependencies();
    }
}
