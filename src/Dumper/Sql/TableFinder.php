<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Doctrine\DBAL\Connection;

/**
 * This class helps to find the available tables which should be dumped.
 */
class TableFinder
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TableDependencies
     */
    private $tableDependencies;

    /**
     * @param Connection                   $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tableDependencies = new TableDependencies();
    }

    /**
     * Returns an array with all available tables which pre-sorted by their dependencies
     * and filtered according to the configuration.
     *
     * @param DumperConfigurationInterface $config
     *
     * @return \Doctrine\DBAL\Schema\Table[]
     */
    public function findTables(DumperConfigurationInterface $config)
    {
        $schemaManager = $this->connection->getSchemaManager();

        $tables = $this->tableDependencies->sortTablesByDependencies($schemaManager->listTables());
        $this->tableDependencies->createDependentFilters($tables, $config);

        $filter = new TableFilter($config);
        $tables = $filter->filterWhiteListTables($tables);
        $tables = $filter->filterIgnoredTables($tables);

        return array_values($tables);
    }
}
