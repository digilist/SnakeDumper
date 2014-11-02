<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DumperInterface
{

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $outputInterface
     *
     * @return mixed
     */
    public function dump(DumperConfigurationInterface $config, OutputInterface $outputInterface);

    /**
     * @param ConverterServiceInterface $dumper
     */
    public function setConverterService(ConverterServiceInterface $dumper);
}
