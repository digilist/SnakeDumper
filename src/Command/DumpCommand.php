<?php

namespace Digilist\SnakeDumper\Command;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\SnakeConfigurationTree;
use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Dumper\DumperInterface;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
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
            ->addOption('disable-structure', null, InputOption::VALUE_NONE, 'Create a dump containing data only')
            ->addOption('host', 'H', InputOption::VALUE_REQUIRED, 'Override the configured host')
            ->addOption('port', 'P', InputOption::VALUE_REQUIRED, 'Override the configured port')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Override the configured user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Override the configured password')
            ->addOption('dbname', null, InputOption::VALUE_REQUIRED, 'Override the name of database that should be dumped')
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
        if ($input->getOption('dbname') !== null) {
            $config->getDatabaseConfig()->setDatabaseName($input->getOption('dbname'));
        }
        if ($input->getOption('user') !== null) {
            $config->getDatabaseConfig()->setUser($input->getOption('user'));
        }
        if ($input->getOption('password') !== null) {
            $config->getDatabaseConfig()->setPassword($input->getOption('password'));
        }
        if ($input->getOption('host') !== null) {
            $config->getDatabaseConfig()->setHost($input->getOption('host'));
        }
        if ($input->getOption('port') !== null) {
            $config->getDatabaseConfig()->setPort($input->getOption('port'));
        }
        if ($input->getOption('disable-limits')) {
            foreach ($config->getTableConfigs() as $tableConfig) {
                $tableConfig->setLimit(null);
            }
        }
        if ($input->getOption('disable-structure')) {
            $config->setIgnoreStructure(true);
        }
    }
}
