<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Dumper;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Table;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StructureDumper
{

    /**
     * @var AbstractPlatform
     */
    private $platform;

    /**
     * @var OutputInterface
     */
    private $dumpOutput;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AbstractPlatform $platform
     * @param OutputInterface  $dumpOutput
     * @param LoggerInterface  $logger
     */
    public function __construct(AbstractPlatform $platform, OutputInterface $dumpOutput, LoggerInterface $logger)
    {
        $this->platform = $platform;
        $this->dumpOutput = $dumpOutput;
        $this->logger = $logger;
    }

    /**
     * @param Table[] $tables
     */
    public function dumpTableStructure(array $tables)
    {
        $this->logger->info('Dumping table structure');
        foreach ($tables as $table) {
            $structure = $this->platform->getCreateTableSQL($table);

            $this->dumpOutput->writeln(implode(";\n", $structure) . ';');
        }
    }

    /**
     * Dump the constraints / foreign keys of all tables.
     *
     * The constraints should be created at the end to speed up the import of the dump.
     *
     * @param Table[]            $tables
     */
    public function dumpConstraints(array $tables) {
        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $constraint) {
                $options = '';
                if ($onUpdate = $constraint->onUpdate()) {
                    $options .= ' ON UPDATE ' . $onUpdate;
                }
                if ($onDelete = $constraint->onDelete()) {
                    $options .= ' ON DELETE ' . $onDelete;
                }
                $constraint = $this->platform->getCreateConstraintSQL($constraint, $table);
                $this->dumpOutput->writeln($constraint . $options . ';');
            }
        }
    }
}
