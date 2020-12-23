<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Dumper;

use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Converter\Service\DataConverterInterface;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use Digilist\SnakeDumper\Dumper\Sql\DataLoader;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TableContentsDumper
{

    /**
     * @var SqlDumperContext
     */
    private $context;

    /**
     * @var ConnectionHandler
     */
    private $connectionHandler;

    /**
     * @var DataConverterInterface
     */
    private $dataConverter;

    /**
     * @var OutputInterface
     */
    private $dumpOutput;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataLoader
     */
    private $dataLoader;

    /**
     * @var array
     */
    private $harvestedValues = [];

    /**
     * @param SqlDumperContext $context
     * @param DataLoader $dataLoader
     */
    public function __construct(SqlDumperContext $context, DataLoader $dataLoader) {
        $this->context = $context;
        $this->dataLoader = $dataLoader;
        $this->connectionHandler = $context->getConnectionHandler();
        $this->dataConverter = $context->getDataConverter();
        $this->dumpOutput = $context->getDumpOutput();
        $this->logger = $context->getLogger();
    }

    /**
     * Dumps the contents of a single table. If the table is ignored, it does not dump anything.
     *
     * @param Table              $table
     * @param TableConfiguration $tableConfig
     */
    public function dumpTable(Table $table, TableConfiguration $tableConfig)
    {
        $this->initValueHarvesting($tableConfig);

        // check if table contents should be ignored
        if ($tableConfig->isContentIgnored()) {
            $this->logger->info('Ignoring contents of table ' . $table->getName());

            return;
        }

        $this->dumpTableContent($table, $tableConfig);
    }

    /**
     * Dumps the contents of a single table.
     *
     * @param Table              $table
     * @param TableConfiguration $tableConfig
     */
    private function dumpTableContent(Table $table, TableConfiguration $tableConfig) {
        // Ensure connection is still open (to prevent for example "MySQL server has gone" errors)
        $this->connectionHandler->reconnectIfNecessary();

        // The PDO instance is used to quote the dumped values.
        $pdo = $this->connectionHandler->getPdo();

        $tableName = $table->getName();
        $quotedTableName = $table->getQuotedName($this->connectionHandler->getPlatform());
        $insertColumns = null;

        $harvestColumns = $tableConfig->getColumnsToHarvest();

        // The buffer is used to create combined SQL statements.
        $maxBufferSize = $this->context->getConfig()->getOutputConfig()->getRowsPerStatement();
        $bufferCount = 0; // number of rows in buffer
        $buffer = array(); // array to buffer rows

        $this->logger->info('Querying table ' . $table->getName());
        $rowCount = $this->dataLoader->countRows($tableConfig, $table, $this->harvestedValues);
        $result = $this->dataLoader->executeSelectQuery($tableConfig, $table, $this->harvestedValues);

        $this->logger->info(sprintf('Dumping table %s (%d rows)', $table->getName(), $rowCount));
        $progress = $this->context
            ->getProgressBarHelper()
            ->createProgressBar($rowCount, OutputInterface::VERBOSITY_VERY_VERBOSE);

        foreach ($result as $row) {
            /*
             * The following code (in this loop) will be executed for each dumped row!
             * Performance in this place is essential!
             */

            foreach ($harvestColumns as $harvestColumn) {
                if (!isset($row[$harvestColumn])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Trying to collect value of column %s in table %s which does not exist.',
                            $harvestColumn,
                            $tableName
                        )
                    );
                }
                $this->harvestedValues[$tableName][$harvestColumn][] = $row[$harvestColumn];
            }

            // Quote all values in the row.
            $context = $row;
            foreach ($row as $key => $value) {
                $value = $this->dataConverter->convert($tableName . '.' . $key, $value, $context);

                if (is_null($value)) {
                    $value = 'NULL';
                } else {
                    $value = $pdo->quote($value);
                }

                $row[$key] = $value;
            }

            if ($insertColumns === null) {
                // As custom queries can be defined to select the data, the column order in the insert statement
                // depends on the actual result set.
                $insertColumns = $this->extractInsertColumns(array_keys($row));
            }

            $buffer[] = '(' . implode(', ', $row) . ')';
            $bufferCount++;

            if ($bufferCount >= $maxBufferSize) {
                $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                    ' VALUES ' . implode(', ', $buffer) . ';';

                $this->dumpOutput->writeln($query);

                $progress->advance($bufferCount);

                $buffer = array();
                $bufferCount = 0;
            }
        }

        if ($bufferCount > 0) {
            // Dump the final buffer.
            $query = 'INSERT INTO ' . $quotedTableName . ' (' . $insertColumns . ')' .
                ' VALUES ' . implode(', ', $buffer) . ';';

            $this->dumpOutput->writeln($query);
        }

        $progress->finish();
    }

    /**
     * Get a SQL-formatted string of the column order to be used in the INSERT statement.
     * (INSERT INTO foo (--> column1, column2, column3, ... <--)
     *
     * @param array $columns
     *
     * @return string
     */
    private function extractInsertColumns($columns)
    {
        $columns = array_map(function ($column) {
            return $this->connectionHandler->getPlatform()->quoteIdentifier($column);
        }, $columns);

        return implode(', ', $columns);
    }

    /**
     * Init the array that is used to harvest values for dependency resolving.
     *
     * @param TableConfiguration $tableConfig
     */
    private function initValueHarvesting(TableConfiguration $tableConfig)
    {
        $this->harvestedValues[$tableConfig->getName()] = array();
        $harvestColumns = $tableConfig->getColumnsToHarvest();
        foreach ($harvestColumns as $harvestColumn) {
            $this->harvestedValues[$tableConfig->getName()][$harvestColumn] = array();
        }
    }
}
