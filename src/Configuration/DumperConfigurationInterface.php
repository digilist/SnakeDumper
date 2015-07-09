<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;

interface DumperConfigurationInterface
{

    /**
     * @return string
     */
    public function getDumper();

    /**
     * @return DatabaseConfiguration
     */
    public function getDatabaseConfig();

    /**
     * @return OutputConfiguration
     */
    public function getOutputConfig();

    /**
     * @return TableConfiguration[]
     */
    public function getTableConfigs();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTableConfig($name);

    /**
     * @param string $name
     *
     * @return TableConfiguration
     */
    public function getTableConfig($name);


    /**
     * @param TableConfiguration $tableConfig
     *
     * @return TableConfiguration
     */
    public function addTableConfig(TableConfiguration $tableConfig);

    /**
     * @return array
     */
    public function getTableWhiteList();

    /**
     * @param array $list
     * @return $this
     */
    public function setTableWhiteList(array $list);

    /**
     * @return void
     */
    public function parseDependencies();
}
