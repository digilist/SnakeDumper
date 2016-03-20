<?php

namespace Digilist\SnakeDumper\Dumper\Helper;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class helps with the creation of a progressbar.
 */
class ProgressBarHelper
{

    /**
     * @var InputInterface
     */
    private $applicationInput;

    /**
     * @var OutputInterface
     */
    private $applicationOutput;

    /**
     * @param InputInterface  $applicationInput
     * @param OutputInterface $applicationOutput
     */
    public function __construct(InputInterface $applicationInput, OutputInterface $applicationOutput)
    {
        $this->applicationInput = $applicationInput;
        $this->applicationOutput = $applicationOutput;
    }

    /**
     * @param int $count
     * @param int $minVerbosity
     *
     * @return ProgressBar
     */
    public function createProgressBar($count, $minVerbosity = OutputInterface::VERBOSITY_NORMAL)
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
