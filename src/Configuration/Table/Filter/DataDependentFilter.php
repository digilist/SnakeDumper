<?php

namespace Digilist\SnakeDumper\Configuration\Table\Filter;

class DataDependentFilter extends ColumnFilter
{

    /**
     * @var string
     */
    private $referencedTable;

    /**
     * @var string
     */
    private $referencedColumn;

    /**
     * DataDependentFilter constructor.
     *
     * @param string $columnName
     * @param string $referencedTable
     * @param string $referencedColumn
     * @param array  $value
     */
    public function __construct($columnName, $referencedTable, $referencedColumn, array $value = [])
    {
        $this->referencedTable = $referencedTable;
        $this->referencedColumn = $referencedColumn;

        parent::__construct($columnName, self::OPERATOR_IN, $value);
    }

    /**

     * @return string
     */
    public function getReferencedTable()
    {
        return $this->referencedTable;
    }

    /**
     * @param string $referencedTable
     *
     * @return $this
     */
    public function setReferencedTable($referencedTable)
    {
        $this->referencedTable = $referencedTable;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferencedColumn()
    {
        return $this->referencedColumn;
    }

    /**
     * @param string $referencedColumn
     *
     * @return $this
     */
    public function setReferencedColumn($referencedColumn)
    {
        $this->referencedColumn = $referencedColumn;

        return $this;
    }
}
