<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Dumper\Context\DumperContextInterface;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\TableContentsDumper;
use Digilist\SnakeDumper\Dumper\Sql\TableSelector;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\StructureDumper;
use Doctrine\DBAL\Schema\Table;

class SqlDumper implements DumperInterface
{

    /**
     * This function starts the dump process.
     *
     * The passed context contains all information that are necessary to create the dump.
     *
     * @param DumperContextInterface|SqlDumperContext $context
     *
     * @return void
     */
    public function dump(DumperContextInterface $context)
    {
        if (!$context instanceof SqlDumperContext) {
            throw new \InvalidArgumentException(
                'The sql dumper requires the context to be instanceof ' . SqlDumperContext::class
            );
        }

        // Retrieve list of tables
        $tableFinder = new TableSelector($context->getConnectionHandler()->getConnection());
        $tables = $tableFinder->findTablesToDump($context->getConfig());

        $structureDumper = new StructureDumper(
            $context->getConnectionHandler()->getPlatform(),
            $context->getDumpOutput(),
            $context->getLogger()
        );

        $this->dumpPreamble($context);
        if (!$context->getConfig()->isIgnoreStructure()) {
            $structureDumper->dumpTableStructure($tables);
        }
        $this->dumpTables($context, $tables);
        $structureDumper->dumpConstraints($tables);
    }

    /**
     * Put some comments and initial commands at the beginning of the dump.
     *
     * @param SqlDumperContext $context
     */
    private function dumpPreamble(SqlDumperContext $context) {
        $dumpOutput = $context->getDumpOutput();

        $commentStart = $context->getConnectionHandler()->getPlatform()->getSqlCommentStartString();
        $dumpOutput->writeln($commentStart . ' ------------------------');
        $dumpOutput->writeln($commentStart . ' SnakeDumper SQL Dump');
        $dumpOutput->writeln($commentStart . ' ------------------------');
        $dumpOutput->writeln('');

        $extras = $context->getConnectionHandler()->getPlatformAdjustment()->getPreembleExtras();
        foreach ($extras as $extra) {
            $dumpOutput->writeln($extra);
        }

        $dumpOutput->writeln('');
    }

    /**
     * @param SqlDumperContext $context
     * @param Table[]          $tables
     */
    private function dumpTables(SqlDumperContext $context, array $tables)
    {
        $progress = $context->getProgressBarHelper()->createProgressBar(count($tables));

        $contentsDumper = new TableContentsDumper($context);
        foreach ($tables as $table) {
            $contentsDumper->dumpTable($table, $context->getConfig()->getTableConfig($table->getName()));
            $progress->advance();
        }

        $progress->finish();
        $context->getLogger()->info('Dump finished');
    }
}
