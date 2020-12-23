<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Dumper\DataLoaderInterface;

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

    /**
     * @param DataLoaderInterface $dataLoader
     * @return mixed
     */
    public function hydrateConfig(DataLoaderInterface $dataLoader);
}
