<?php

namespace Digilist\SnakeDumper\Dumper\Context;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Dumper\Helper\ProgressBarHelper;
use Digilist\SnakeDumper\Output\GzipStreamOutput;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

abstract class AbstractDumperContext implements DumperContextInterface
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
     * @var ProgressBarHelper
     */
    protected $progressBarHelper;

    /**
     * @param DumperConfigurationInterface $config
     * @param InputInterface               $applicationInput
     * @param OutputInterface              $applicationOutput
     */
    public function __construct(
        DumperConfigurationInterface $config,
        InputInterface $applicationInput,
        OutputInterface $applicationOutput
    ) {
        $this->config = $config;
        $this->applicationInput = $applicationInput;
        $this->applicationOutput = $applicationOutput;

        $this->dumpOutput = $this->createDumpOutputStream($config);
        $this->logger = $this->createLogger($applicationOutput);

        $this->progressBarHelper = new ProgressBarHelper($applicationInput, $applicationOutput);
    }

    /**
     * @return DumperConfigurationInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return OutputInterface
     */
    public function getDumpOutput()
    {
        return $this->dumpOutput;
    }

    /**
     * @param OutputInterface $dumpOutput
     *
     * @return $this
     */
    public function setDumpOutput(OutputInterface $dumpOutput)
    {
        $this->dumpOutput = $dumpOutput;

        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getApplicationOutput()
    {
        return $this->applicationOutput;
    }

    /**
     * @return InputInterface
     */
    public function getApplicationInput()
    {
        return $this->applicationInput;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return ProgressBarHelper
     */
    public function getProgressBarHelper()
    {
        return $this->progressBarHelper;
    }

    /**
     * @param DumperConfigurationInterface $config
     *
     * @return Output|null
     */
    private function createDumpOutputStream(DumperConfigurationInterface $config)
    {
        if (empty($config->getOutputConfig()->getFile())) {
            return null; // in this case, the output has to be set via setDumpOutput()
        }

        if ($config->getOutputConfig()->getGzip()) {
            return new GzipStreamOutput(gzopen($config->getOutputConfig()->getFile(), 'w'));
        }

        return new StreamOutput(fopen($config->getOutputConfig()->getFile(), 'w'));
    }

    /**
     * @param OutputInterface $applicationOutput
     *
     * @return LoggerInterface
     */
    private function createLogger(OutputInterface $applicationOutput)
    {
        if ($applicationOutput->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return new NullLogger();
        }

        $logger = new Logger('snakedumper');
        $logger->pushHandler(new StreamHandler(fopen('php://stderr', 'w')));

        return $logger;
    }
}
