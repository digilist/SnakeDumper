<?php

namespace Digilist\SnakeDumper\Command;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Configuration\SnakeConfigurationTree;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\DumperInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfig($input);
        $context = new SqlDumperContext(
            $config,
            $input,
            $output
        );

        /** @var DumperInterface $dumper */
        $class = $config->getFullQualifiedDumperClassName();
        $dumper = new $class();
        $dumper->dump($context);
    }

    /**
     * @param InputInterface $input
     *
     * @return SqlDumperConfiguration
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

        $config = Yaml::parse(file_get_contents($configFile));

        $processor = new Processor();
        $configuration = new SnakeConfigurationTree();
        $processed = $processor->processConfiguration($configuration, array($config));

        $config = new SqlDumperConfiguration($processed);
        $this->overrideConfigs($config, $input);

        return $config;
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
