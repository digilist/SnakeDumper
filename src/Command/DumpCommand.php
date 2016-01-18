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
            ->addOption('progress', null, InputOption::VALUE_NONE, 'Show a progress bar')
            ->addOption('disable-limits', null, InputOption::VALUE_NONE, 'Create a full database dump')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Override the configured password')
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

    /**
     * @param InputInterface $input
     *
     * @return DumperConfiguration
     */
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

        $config = new DumperConfiguration($processed);
        $this->overrideConfigs($config, $input);

        return $config;
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

    /**
     * Override some variables in the config with parameters from the command line.
     *
     * @param DumperConfigurationInterface $config
     * @param InputInterface               $input
     */
    private function overrideConfigs(DumperConfigurationInterface $config, InputInterface $input)
    {
        if ($input->hasOption('password')) {
            $config->getDatabaseConfig()->setPassword($input->getOption('password'));
        }
        if ($input->getOption('disable-limits')) {
            foreach ($config->getTableConfigs() as $tableConfig) {
                $tableConfig->setLimit(null);
            }
        }
    }
}
