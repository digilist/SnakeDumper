<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DumperInterface
{

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $dumpOutput
     * @param InputInterface               $applicationInput
     * @param OutputInterface              $applicationOutput
     */
    public function __construct(
        DumperConfigurationInterface $config,
        OutputInterface $dumpOutput,
        InputInterface $applicationInput,
        OutputInterface $applicationOutput
    );

    /**
     * @return mixed
     */
    public function dump();

    /**
     * @param ConverterServiceInterface $dumper
     */
    public function setConverterService(ConverterServiceInterface $dumper);
}
