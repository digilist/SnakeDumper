<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Digilist\SnakeDumper\Dumper\Helper\ProgressBarHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractDumper implements DumperInterface
{

    /**
     * @var DumperConfigurationInterface
     */
    protected $config;

    /**
     * @var OutputInterface
     */
    protected $dumpOutput;

    /**
     * @var OutputInterface
     */
    protected $applicationOutput;

    /**
     * @var InputInterface
     */
    protected $applicationInput;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConverterServiceInterface
     */
    protected $converterService;

    /**
     * @var ProgressBarHelper
     */
    protected $progressBarHelper;

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
    ) {
        $this->config = $config;
        $this->applicationInput = $applicationInput;
        $this->applicationOutput = $applicationOutput;
        $this->dumpOutput = $dumpOutput;

        if ($applicationOutput->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->logger = new Logger('snakedumper');
            $this->logger->pushHandler(new StreamHandler(fopen('php://stderr', 'w')));
        } else {
            $this->logger = new NullLogger();
        }

        $this->progressBarHelper = new ProgressBarHelper($applicationInput, $applicationOutput);;
    }

    /**
     * @param ConverterServiceInterface $converterService
     */
    public function setConverterService(ConverterServiceInterface $converterService)
    {
        $this->converterService = $converterService;
    }

    /**
     * @param int $count
     * @param int $minVerbosity
     *
     * @return ProgressBar
     */
    protected function createProgressBar($count, $minVerbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        return $this->progressBarHelper->createProgressBar($count, $minVerbosity);
    }
}
