<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DumperInterface
{

    public function dump(DumperConfigurationInterface $config, OutputInterface $outputInterface);

    public function setConverter(ConverterServiceInterface $dumper);
}
