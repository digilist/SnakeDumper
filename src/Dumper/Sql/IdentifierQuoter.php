<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

/**
 * This class quotes all identifiers of tables, columns and foreign keys.
 */
class IdentifierQuoter
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    /**
     * @param Connection       $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->platform = $connection->getDatabasePlatform();
    }

    /**
     * Quotes all names / identifiers of the tables and their columns and foreign keys.
     * Anew set with of tables is returned.
     *
     * @param Table[] $unquotedTables
     * @return Table[]
     */
    public function quoteTables(array $unquotedTables)
    {
        /** @var Table[] $tables */
        $tables = array();
        foreach ($unquotedTables as $table) {
            $columns = array();
            foreach ($table->getColumns() as $column) {
                $columns[] = $this->createColumnReplacement($column);
            }

            $indexes = array();
            foreach ($table->getIndexes() as $index) {
                $indexes[] = $this->createIndexReplacement($index);
            }

            $foreignKeys = array();
            foreach ($table->getForeignKeys() as $fk) {
                $foreignKeys[] = $this->createForeignKeyReplacement($fk);
            }

            $tables[] = new Table(
                $this->platform->quoteIdentifier($table->getName()),
                $columns,
                $indexes,
                $foreignKeys,
                false,
                array()
            );
        }

        return $tables;
    }

    /**
     * Creates a column replacement, which has a quoted name.
     *
     * @param Column $column
     *
     * @return Column
     */
    private function createColumnReplacement(Column $column)
    {
        $columnConfig = $column->toArray();
        $columnConfig['platformOptions'] = $column->getPlatformOptions();
        $columnConfig['customSchemaOptions'] = $column->getCustomSchemaOptions();

        return new Column(
            $this->platform->quoteIdentifier($column->getName()),
            $column->getType(),
            $columnConfig
        );
    }

    /**
     * Creates a index replacement, which has quoted names.
     *
     * @param Index $index
     *
     * @return Index
     */
    private function createIndexReplacement(Index $index)
    {
        return new Index(
            $this->platform->quoteIdentifier($index->getName()),
            $this->quoteIdentifiers($index->getColumns()),
            $index->isUnique(),
            $index->isPrimary(),
            $index->getFlags(),
            $index->getOptions()
        );
    }

    /**
     * Creates a foreign index replacement, which has quoted column names.
     *
     * @param ForeignKeyConstraint $fk
     *
     * @return ForeignKeyConstraint
     */
    private function createForeignKeyReplacement(ForeignKeyConstraint $fk)
    {
        return new ForeignKeyConstraint(
            $this->quoteIdentifiers($fk->getLocalColumns()),
            $this->platform->quoteIdentifier($fk->getForeignTableName()),
            $this->quoteIdentifiers($fk->getForeignColumns()),
            $this->platform->quoteIdentifier($fk->getName()),
            $fk->getOptions()
        );
    }

    /**
     * Quote multiple identifiers.
     *
     * @param array $identifiers
     *
     * @return string[]
     */
    private function quoteIdentifiers(array $identifiers)
    {
        return array_map(array($this->platform, 'quoteIdentifier'), $identifiers);
    }
}
