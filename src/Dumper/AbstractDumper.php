<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
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
    private $converterService;

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
        $this->applicationOutput = $applicationOutput;
        $this->applicationInput = $applicationInput;
        $this->dumpOutput = $dumpOutput;

        if ($applicationOutput->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->logger = new Logger('snakedumper');
            $this->logger->pushHandler(new StreamHandler(fopen('php://stderr', 'w')));
        } else {
            $this->logger = new NullLogger();
        }
    }

    /**
     * @param ConverterServiceInterface $converterService
     */
    public function setConverterService(ConverterServiceInterface $converterService)
    {
        $this->converterService = $converterService;
    }

    /**
     * @param string  $key
     * @param string  $value
     * @param array   $context
     *
     * @return mixed
     */
    protected function convert($key, $value, array $context = array())
    {
        return $this->converterService->convert($key, $value, $context);
    }

    /**
     * @param int $count
     * @param int $minVerbosity
     *
     * @return ProgressBar
     */
    protected function createProgressBar($count, $minVerbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $stream = new NullOutput();
        if ($this->applicationInput->hasOption('progress') && $this->applicationInput->getOption('progress')) {
            if ($this->applicationOutput instanceof ConsoleOutput) {
                if ($this->applicationOutput->getVerbosity() >= $minVerbosity) {
                    $stream = $this->applicationOutput->getErrorOutput();
                }
            }
        }

        $progress = new ProgressBar($stream, $count);
        // add an additional space, in case logging is also enabled
        $progress->setFormat($progress->getFormatDefinition('normal') . ' ');
        $progress->start();

        return $progress;
    }
}
