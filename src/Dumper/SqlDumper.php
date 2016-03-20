<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\SqlConverterService;
use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\TableContentsDumper;
use Digilist\SnakeDumper\Dumper\Sql\TableSelector;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\StructureDumper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Table;
use PDO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SqlDumper extends AbstractDumper
{

    /**
     * @var ConnectionHandler
     */
    private $connectionHandler;

    /**
     * @var array
     */
    private $harvestedValues = array();

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $dumpOutput
     * @param InputInterface               $applicationInput
     * @param OutputInterface              $applicationOutput
     * @param Connection                   $connection
     */
    public function __construct(
        DumperConfigurationInterface $config,
        OutputInterface $dumpOutput,
        InputInterface $applicationInput,
        OutputInterface $applicationOutput,
        Connection $connection = null
    ) {
        parent::__construct($config, $dumpOutput, $applicationInput, $applicationOutput);

        $this->setConverterService(SqlConverterService::createFromConfig($config));
        $this->connectionHandler = new ConnectionHandler($config->getDatabaseConfig(), $connection);
    }


    /**
     * {@inheritdoc}
     */
    public function dump()
    {
        // Retrieve list of tables
        $tableFinder = new TableSelector($this->connectionHandler->getConnection());
        $tables = $tableFinder->selectTables($this->config);

        $structureDumper = new StructureDumper(
            $this->connectionHandler->getPlatform(),
            $this->dumpOutput,
            $this->logger
        );

        $this->dumpPreamble();
        $structureDumper->dumpTableStructure($tables);
        $this->dumpTables($tables);
        $structureDumper->dumpConstraints($tables);
    }

    /**
     */
    private function dumpPreamble() {
        $commentStart = $this->connectionHandler->getPlatform()->getSqlCommentStartString();
        $this->dumpOutput->writeln($commentStart . ' ------------------------');
        $this->dumpOutput->writeln($commentStart . ' SnakeDumper SQL Dump');
        $this->dumpOutput->writeln($commentStart . ' ------------------------');
        $this->dumpOutput->writeln('');

        $extras = $this->connectionHandler->getPlatformAdjustment()->getPreembleExtras();
        foreach ($extras as $extra) {
            $this->dumpOutput->writeln($extra);
        }

        $this->dumpOutput->writeln('');
    }

    /**
     * @param Table[] $tables
     */
    private function dumpTables(array $tables)
    {
        $progress = $this->createProgressBar(count($tables));

        $contentsDumper = new TableContentsDumper(
            $this->connectionHandler,
            $this->converterService,
            $this->dumpOutput,
            $this->config->getOutputConfig()->getRowsPerStatement(),
            $this->progressBarHelper,
            $this->logger
        );

        foreach ($tables as $table) {
            $contentsDumper->dumpTable($table, $this->config->getTableConfig($table->getName()));
            $progress->advance();
        }

        $progress->finish();
        $this->logger->info('Dump finished');
    }
}
