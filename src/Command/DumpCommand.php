<?php

namespace Digilist\SnakeDumper\Command;

use Digilist\SnakeDumper\Configuration\SnakeConfigurationTree;
use Digilist\SnakeDumper\Exporter\SqlDumper;
use Digilist\SnakeDumper\Logger\PsrSQLLogger;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Visitor\CreateSchemaSqlCollector;
use Doctrine\DBAL\Types\Type;
use PDO;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dump')
            ->addOption('config', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfig($input);

        $dumper = new SqlDumper();
        $dumper->dump($config);
    }

    private function parseConfig(InputInterface $input)
    {
        $config = Yaml::parse(sprintf('%s/%s', getcwd(), $input->getOption('config')));

        $processor = new Processor();
        $configuration = new SnakeConfigurationTree();
        return $processor->processConfiguration($configuration, [$config]);
    }
}
