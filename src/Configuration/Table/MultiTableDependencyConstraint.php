<?php


namespace Digilist\SnakeDumper\Configuration\Table;


class MultiTableDependencyConstraint
{

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var string $referencedTable
     */
    private $columnReferencedTable;

    /**
     * @var string $referencedColumn
     */
    private $referencedColumn;

    /**
     * MultiTableDependencyConstraint constructor.
     * @param string $column
     * @param string $columnReferencedTable
     * @param string $referencedColumn
     */
    public function __construct($columnReferencedTable, $referencedColumn, $column)
    {
        $this->column = $column;
        $this->columnReferencedTable = $columnReferencedTable;
        $this->referencedColumn = $referencedColumn;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getColumnReferencedTable()
    {
        return $this->columnReferencedTable;
    }

    /**
     * @return string
     */
    public function getReferencedColumn()
    {
        return $this->referencedColumn;
    }




}