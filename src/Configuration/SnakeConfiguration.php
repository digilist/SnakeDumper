<?php

namespace Digilist\SnakeDumper\Configuration;

class SnakeConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
{

    private $database;
    private $output;

    /**
     * @var TableConfiguration[]
     */
    private $tables = array();

    /**
     * @return DatabaseConfiguration
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return TableConfiguration[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getTableWhiteList()
    {
        return $this->get('table_white_list', array());
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTable($name)
    {
        return array_key_exists($name, $this->tables);
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
        if (!array_key_exists($name, $this->tables)) {
            return null;
        }

        return $this->tables[$name];
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
    public function getFullQualifiedDumper()
    {
        return 'Digilist\\SnakeDumper\\Dumper\\' . $this->getDumper() . 'Dumper';
    }

    /**
     * @return OutputConfiguration
     */
    public function getOutput()
    {
        return $this->output;
    }

    protected function parseConfig(array $config)
    {
        $this->database = new DatabaseConfiguration($this->get('database', null));
        $this->output = new OutputConfiguration($this->get('output', null));

        // parse tables
        foreach ($this->get('tables', array()) as $name => $table) {
            $this->tables[$name] = new TableConfiguration($name, $table);
        }
    }
}
