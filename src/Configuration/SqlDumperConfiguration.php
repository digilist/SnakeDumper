<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableDependencyConstraint;
use Digilist\SnakeDumper\Dumper\DataLoaderInterface;

class SqlDumperConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
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
     * @var bool
     */
    private $ignoreStructure = false;

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
        return sprintf('Digilist\\SnakeDumper\\Dumper\\%sDumper', ucfirst($this->getDumper()));
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
            /** @var TableDependencyConstraint $dependency */
            foreach ($table->getDependentTables() as $dependency) {
                $dependentTable = $dependency->getReferencedTable();
                if (!array_key_exists($dependentTable, $this->tableConfigs)) {
                    $this->tableConfigs[$dependentTable] = new TableConfiguration($dependentTable, array());
                }
                $this->tableConfigs[$dependentTable]->addHarvestColumn($dependency->getReferencedColumn());
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
    }

    public function hydrateConfig(DataLoaderInterface $dataLoader) {
        foreach ($this->tableConfigs as $tableConfiguration) {
            $tableConfiguration->hydrateConfig($dataLoader);
        }
        $this->parseDependencies();
    }

    /**
     * Get ignore structure.
     *
     * @return bool
     */
    public function isIgnoreStructure()
    {
        return $this->ignoreStructure;
    }

    /**
     * Set ignore structure.
     *
     * @param bool $ignoreStructure
     */
    public function setIgnoreStructure($ignoreStructure)
    {
        $this->ignoreStructure = $ignoreStructure;
    }
}
