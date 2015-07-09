<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DumperInterface
{

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $output
     * @param LoggerInterface              $logger
     */
    public function __construct(DumperConfigurationInterface $config, OutputInterface $output, LoggerInterface $logger);

    /**
     * @return mixed
     */
    public function dump();

    /**
     * @param ConverterServiceInterface $dumper
     */
    public function setConverterService(ConverterServiceInterface $dumper);
}
