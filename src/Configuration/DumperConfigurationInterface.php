<?php

namespace Digilist\SnakeDumper\Configuration;

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
}
