<?php

namespace Digilist\SnakeDumper\Command;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\DumperConfiguration;
use Digilist\SnakeDumper\Configuration\SnakeConfigurationTree;
use Digilist\SnakeDumper\Dumper\DumperInterface;
use Digilist\SnakeDumper\Output\GzipStreamOutput;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Yaml\Yaml;

class DumpCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('dump')
            ->addArgument('config', InputArgument::REQUIRED, 'The path to your config file.')
            ->addOption('progress', 'p', InputOption::VALUE_NONE, 'Show a progress bar')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfig($input);

        /** @var DumperInterface $dumper */
        $class = $config->getFullQualifiedDumperClassName();
        $dumper = new $class($config, $this->getOutputStream($config), $input, $output);
        $dumper->dump();
    }

    private function parseConfig(InputInterface $input)
    {
        $configArg = $input->getArgument('config');
        if (strpos($configArg, '/') === 0) {
            $configFile = realpath($configArg);
        } else {
            $configFile = realpath(sprintf('%s/%s', getcwd(), $configArg));
        }

        if (!$configFile) {
            throw new \InvalidArgumentException('Cannot find configuration file: ' . $configArg);
        }

        $config = Yaml::parse($configFile);

        $processor = new Processor();
        $configuration = new SnakeConfigurationTree();
        $processed = $processor->processConfiguration($configuration, array($config));

        return new DumperConfiguration($processed);
    }

    /**
     * Returns the target output interface for the dump.
     *
     * @param DumperConfigurationInterface $config
     *
     * @return Output
     */
    private function getOutputStream(DumperConfigurationInterface $config)
    {
        if ($config->getOutputConfig()->getGzip()) {
            return new GzipStreamOutput(gzopen($config->getOutputConfig()->getFile(), 'w'));
        }

        return new StreamOutput(fopen($config->getOutputConfig()->getFile(), 'w'));
    }
}
