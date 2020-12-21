<?php

namespace Digilist\SnakeDumper\Configuration\Table;


use Digilist\SnakeDumper\Configuration\Table\Filter\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\CompositeFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\FilterFactory;
use Digilist\SnakeDumper\Configuration\Table\Filter\FilterInterface;
use InvalidArgumentException;

class TableDependencyConstraint {

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var string $referencedTable
     */
    private $referencedTable;

    /**
     * @var string $referencedColumn
     */
    private $referencedColumn;

    /**
     * @var string $condition
     */
    private $condition;

    public function __construct($referencedTable, $referencedColumn = null, $column = null, $condition = null)
    {
        $this->column = $column;
        $this->referencedTable = $referencedTable;
        $this->referencedColumn = $referencedColumn;
        $this->condition = $condition;
    }

    public static function createFromFilterConfig($filterConfig)
    {
        $operator = $filterConfig[0];
        if ($operator == 'depends') {
            $columnName = $filterConfig[1];
            $value = $filterConfig[2];
            $referencedColumn = explode('.', $value);
            if (count($referencedColumn) !== 2) {
                throw new InvalidArgumentException(
                    'Unexpected format for depends operator "' . $value . '". Expected format "table.column"'
                );
            }

            $referencedTable = $referencedColumn[0];
            $referencedColumn = $referencedColumn[1];

            return new TableDependencyConstraint($referencedTable, $referencedColumn, $columnName);
        }
        return null;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter() {
        $dependencyFilter = new DataDependentFilter($this->getColumn(), $this->getReferencedTable(), $this->getReferencedColumn());
        if ($this->getCondition()) {
            return new CompositeFilter(CompositeFilter::OPERATOR_AND, [
                $dependencyFilter,
                FilterFactory::buildFilter($this->getCondition())
            ]);
        }
        return $dependencyFilter;
    }


    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
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
     */
    public function setReferencedTable($referencedTable)
    {
        $this->referencedTable = $referencedTable;
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
     */
    public function setReferencedColumn($referencedColumn)
    {
        $this->referencedColumn = $referencedColumn;
    }

    /**
     * @return array
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param array $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

}