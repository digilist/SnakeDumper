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
    public function __construct(DumperConfigurationInterface $config, OutputInterface $outputInterface);

    /**
     * @return mixed
     */
    public function dump();

    /**
     * @param ConverterServiceInterface $dumper
     */
    public function setConverterService(ConverterServiceInterface $dumper);
}
