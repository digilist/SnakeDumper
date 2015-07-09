<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Doctrine\DBAL\Schema\Table;

class TableFilter
{

    private $config;

    /**
     * @param DumperConfigurationInterface $config
     */
    public function __construct(DumperConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param Table[] $tables
     * @return Table[]
     */
    public function filterWhiteListTables(array $tables)
    {
        // Return all tables if white list is empty
        $whiteList = $this->config->getTableWhiteList();
        if (empty($whiteList)) {
            return $tables;
        }

        return array_filter($tables, array($this, 'isTableWhiteListed'));
    }

    /**
     * @param Table[] $tables
     * @return Table[]
     */
    public function filterIgnoredTables(array $tables)
    {
        return array_filter($tables, array($this, 'isTableNotIgnored'));
    }

    /**
     * Filter closure to determine white listed tables.
     *
     * @param Table $table
     * @return bool
     */
    public function isTableWhiteListed(Table $table)
    {
        $whiteList = $this->config->getTableWhiteList();

        if (in_array($table->getName(), $whiteList)) {
            return true;
        }

        // Check against wildcards etc.
        foreach ($whiteList as $entry) {
            if (fnmatch($entry, $table->getName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Table $table
     * @return bool
     */
    public function isTableNotIgnored(Table $table) {
        if (!$this->config->hasTableConfig($table->getName())) {
            return true;
        }

        return !$this->config->getTableConfig($table->getName())->isTableIgnored();
    }
}
