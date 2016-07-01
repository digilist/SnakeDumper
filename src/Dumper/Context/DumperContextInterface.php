<?php
namespace Digilist\SnakeDumper\Dumper\Context;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\DataConverterInterface;
use Digilist\SnakeDumper\Dumper\Helper\ProgressBarHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The dumper context has the purpose to contain all necessary information and dependencies that need to be passed
 * around between different dump operations.
 */
interface DumperContextInterface
{

    /**
     * @return OutputInterface
     */
    public function getDumpOutput();

    /**
     * @return OutputInterface
     */
    public function getApplicationOutput();

    /**
     * @return InputInterface
     */
    public function getApplicationInput();

    /**
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * @return DataConverterInterface
     */
    public function getDataConverter();

    /**
     * @return ProgressBarHelper
     */
    public function getProgressBarHelper();

    /**
     * @return DumperConfigurationInterface
     */
    public function getConfig();
}
