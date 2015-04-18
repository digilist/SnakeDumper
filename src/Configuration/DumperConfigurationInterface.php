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
    public function getDatabase();

    /**
     * @return OutputConfiguration
     */
    public function getOutput();

    /**
     * @return TableConfiguration[]
     */
    public function getTables();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTable($name);

    /**
     * @param string $name
     *
     * @return TableConfiguration
     */
    public function getTable($name);

    /**
     * @return array
     */
    public function getTableWhiteList();

    /**
     * @param array $list
     * @return $this
     */
    public function setTableWhiteList(array $list);
}
