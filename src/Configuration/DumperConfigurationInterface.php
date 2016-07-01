<?php

namespace Digilist\SnakeDumper\Configuration;

interface DumperConfigurationInterface
{

    /**
     * @return string
     */
    public function getDumper();

    /**
     * @return OutputConfiguration
     */
    public function getOutputConfig();
}
