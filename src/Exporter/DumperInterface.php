<?php

namespace Digilist\SnakeDumper\Exporter;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;

interface DumperInterface
{

    public function dump(DumperConfigurationInterface $config);

} 
