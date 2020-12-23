<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Dumper\Context\DumperContextInterface;
use Digilist\SnakeDumper\Dumper\Sql\DataLoader;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\TableContentsDumper;
use Digilist\SnakeDumper\Dumper\Sql\TableSelector;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\StructureDumper;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;

class SqlDumper implements DumperInterface
{

    /** @var SqlDumperContext $context */
    protected $context;

    /** @var DataLoader $dataLoader */
    protected $dataLoader;

    /**
     * SqlDumper constructor.
     * @param DumperContextInterface $this->context
     */
    public function __construct(DumperContextInterface $context)
    {
        if (!$context instanceof SqlDumperContext) {
            throw new InvalidArgumentException(
                'The sql dumper requires the context to be instanceof ' . SqlDumperContext::class
            );
        }
        $this->context = $context;
        $this->dataLoader = new DataLoader($context->getConnectionHandler(), $context->getLogger());
    }

    /**
     * This function starts the dump process.
     *
     * The passed context contains all information that are necessary to create the dump.
     *
     * @return void
     * @throws \Exception
     */
    public function dump()
    {
        $this->context->getConfig()->hydrateConfig($this->dataLoader);

        // Retrieve list of tables
        $tableFinder = new TableSelector($this->context->getConnectionHandler()->getConnection());
        $tables = $tableFinder->findTablesToDump($this->context->getConfig());

        $structureDumper = new StructureDumper(
            $this->context->getConnectionHandler()->getPlatform(),
            $this->context->getDumpOutput(),
            $this->context->getLogger()
        );

        $this->dumpPreamble();

        if (!$this->context->getConfig()->isIgnoreStructure()) {
            $structureDumper->dumpTableStructure($tables);
        }

        $this->dumpTables($tables);

        if (!$this->context->getConfig()->isIgnoreStructure()) {
            $structureDumper->dumpConstraints($tables);
        }
    }

    /**
     * Put some comments and initial commands at the beginning of the dump.
     *
     */
    private function dumpPreamble() {
        $dumpOutput = $this->context->getDumpOutput();

        $commentStart = $this->context->getConnectionHandler()->getPlatform()->getSqlCommentStartString();
        $dumpOutput->writeln($commentStart . ' ------------------------');
        $dumpOutput->writeln($commentStart . ' SnakeDumper SQL Dump');
        $dumpOutput->writeln($commentStart . ' ------------------------');
        $dumpOutput->writeln('');

        $extras = $this->context->getConnectionHandler()->getPlatformAdjustment()->getPreembleExtras();
        foreach ($extras as $extra) {
            $dumpOutput->writeln($extra);
        }

        $dumpOutput->writeln('');
    }

    /**
     * @param Table[]          $tables
     */
    private function dumpTables(array $tables)
    {
        $progress = $this->context->getProgressBarHelper()->createProgressBar(count($tables));

        $contentsDumper = new TableContentsDumper($this->context, $this->dataLoader);
        foreach ($tables as $table) {
            $contentsDumper->dumpTable($table, $this->context->getConfig()->getTableConfig($table->getName()));
            $progress->advance();
        }

        $progress->finish();
        $this->context->getLogger()->info('Dump finished');
    }
}
