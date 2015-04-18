<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Configuration\Table\DataDependentFilterConfiguration;
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
     * Returns the table for configuration of the table with the passed name. If there is no configuration,
     * null will be returned.
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

    protected function parseConfig(array $config)
    {
        $this->databaseConfiguration = new DatabaseConfiguration($this->get('database', null));
        $this->outputConfiguration = new OutputConfiguration($this->get('output', null));

        // parse tables
        foreach ($this->get('tables', array()) as $name => $tableConfig) {
            $this->tableConfigurations[$name] = new TableConfiguration($name, $tableConfig);
        }

        // parse dependent columns
        foreach ($this->tableConfigurations as $table) {
            foreach ($table->getDependencies() as $dependency) {
                if (!array_key_exists($dependency, $this->tableConfigurations)) {
                    $this->tableConfigurations[$dependency] = new TableConfiguration($dependency, array());
                }

                foreach ($table->getColumns() as $config) {
                    foreach ($config->getFilters() as $filter) {
                        if ($filter instanceof DataDependentFilterConfiguration) {
                            $this->tableConfigurations[$dependency]->addCollectColumns($filter->getColumn());
                        }
                    }
                }
            }
        }
    }
}
