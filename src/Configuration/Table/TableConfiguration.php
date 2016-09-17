<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;
use Digilist\SnakeDumper\Configuration\Table\Filter\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\DefaultFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\FilterInterface;

class TableConfiguration extends AbstractConfiguration
{

    /**
     * @var string
     */
    private $name;

    /**
     * Contains the names of all dependent tables
     *
     * @var string[]
     */
    private $dependentTables = array();

    /**
     * Contains the names of all columns which values should be collected / harvested for later reuse.
     *
     * @var array
     */
    private $columnsToHarvest = array();

    /**
     * Converters grouped by column
     *
     * @var ConverterDefinition[][]
     */
    private $convertersDefinitions = array();

    /**
     * Filters
     *
     * @var FilterInterface[]
     */
    private $filters = array();

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config = array())
    {
        $this->name = $name;

        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     * @return string
     */
    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTableIgnored()
    {
        return $this->get('ignore_table', false);
    }

    /**
     * @param bool $ignore
     * @return $this
     */
    public function setIgnoreTable($ignore = true)
    {
        return $this->set('ignore_table', (bool) $ignore);
    }

    /**
     * @return bool
     */
    public function isContentIgnored()
    {
        return $this->get('ignore_content', false);
    }

    /**
     * @param bool $ignore
     * @return $this
     */
    public function setIgnoreContent($ignore = true)
    {
        return $this->set('ignore_content', (bool) $ignore);
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->get('order_by');
    }

    /**
     * @param string $orderBy
     *
     * @return $this
     */
    public function setOrderBy($orderBy)
    {
        return $this->set('order_by', $orderBy);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->get('limit');
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        return $this->set('limit', $limit);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->get('query');
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public function setQuery($query)
    {
        return $this->set('query', $query);
    }

    /**
     * @return array
     */
    public function getDependentTables()
    {
        return $this->dependentTables;
    }

    /**
     * @return array
     */
    public function hasDependentTables()
    {
        return count($this->dependentTables) > 0;
    }

    /**
     * @param array $dependentTables
     *
     * @return $this
     */
    public function setDependentTables(array $dependentTables)
    {
        $this->dependentTables = $dependentTables;

        return $this;
    }

    /**
     * @param string $dependency
     *
     * @return $this
     */
    public function addDependency($dependency)
    {
        $this->dependentTables[] = $dependency;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumnsToHarvest()
    {
        return $this->columnsToHarvest;
    }

    /**
     * @param array $columnsToHarvest
     *
     * @return $this
     */
    public function setColumnsToHarvest($columnsToHarvest)
    {
        $this->columnsToHarvest = array_unique($columnsToHarvest);

        return $this;
    }

    /**
     * @return ConverterDefinition[][]
     */
    public function getConverters()
    {
        return $this->convertersDefinitions;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param $column
     *
     * @return FilterInterface[]
     */
    public function getFiltersByColumn($column)
    {
        if (!isset($this->filters[$column])) {
            return array();
        }

        return $this->filters[$column];
    }

    /**
     * @param array $collectColumn
     *
     * @return $this
     */
    public function addHarvestColumn($collectColumn)
    {
        $this->columnsToHarvest[] = $collectColumn;
        $this->columnsToHarvest = array_unique($this->columnsToHarvest);

        return $this;
    }

    protected function parseConfig(array $config)
    {
        if (!isset($config['converters'])) {
            $config['converters'] = [];
        }
        if (!isset($config['filters'])) {
            $config['filters'] = [];
        }

        // Parse converter definitions and create objects
        foreach ($config['converters'] as $columnName => $converters) {
            foreach ($converters as $converterDef) {
                $this->convertersDefinitions[$columnName][] = ConverterDefinition::factory($converterDef);
            }
        }

        // Parse filter configurations and create filter objects
        foreach ($config['filters'] as $filter) {
            $operator = $filter[0];
            $columnName = $filter[1];
            $value = $filter[2];

            if ($operator == 'depends') {
                $referencedColumn = explode('.', $value);
                if (count($referencedColumn) !== 2) {
                    throw new \InvalidArgumentException(
                        'Unexpected format for depends operator "' . $value . '". Expected format "table.column"'
                    );
                }

                $referencedTable = $referencedColumn[0];
                $referencedColumn = $referencedColumn[1];

                $this->dependentTables[] = $referencedTable;

                $this->filters[] = new DataDependentFilter($columnName, $referencedTable, $referencedColumn);
            } else {
                $this->filters[] = new DefaultFilter($columnName, $operator, $value);
            }
        }
    }
}
