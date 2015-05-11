<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;

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
    private $dependencies = array();

    /**
     * Contains the names of all columns which values should be collected / harvested for later reuse.
     *
     * @var array
     */
    private $harvestColumns = array();

    /**
     * Converters grouped by column
     *
     * @var ConverterConfiguration[][]
     */
    private $converters = array();

    /**
     * Filters grouped by column
     *
     * @var FilterConfiguration[][]
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
        return $this->get('orderBy');
    }

    /**
     * @param string $orderBy
     *
     * @return $this
     */
    public function setOrderBy($orderBy)
    {
        return $this->set('orderBy', $orderBy);
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
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return array
     */
    public function hasDependencies()
    {
        return count($this->dependencies) > 0;
    }

    /**
     * @param array $dependencies
     *
     * @return $this
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    /**
     * @param string $dependency
     *
     * @return $this
     */
    public function addDependency($dependency)
    {
        $this->dependencies[] = $dependency;

        return $this;
    }

    /**
     * @return array
     */
    public function getHarvestColumns()
    {
        return $this->harvestColumns;
    }

    /**
     * @param array $collectColumns
     *
     * @return $this
     */
    public function setHarvestColumns($collectColumns)
    {
        $this->harvestColumns = array_unique($collectColumns);

        return $this;
    }

    /**
     * @return ConverterConfiguration[][]
     */
    public function getConverters()
    {
        return $this->converters;
    }

    /**
     * @param $column
     *
     * @return ConverterConfiguration[]
     */
    public function getConvertersByColumn($column)
    {
        if (!isset($this->converters[$column])) {
            return array();
        }

        return $this->converters[$column];
    }

    /**
     * @return FilterConfiguration[][]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $column
     *
     * @return FilterConfiguration[]
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
        $this->harvestColumns[] = $collectColumn;
        $this->harvestColumns = array_unique($this->harvestColumns);

        return $this;
    }

    protected function parseConfig(array $config)
    {
        $columns = array();

        foreach ($this->get('converters', array()) as $columnName => $converters) {
            foreach ($converters as $converterDef) {
                $parameter = null;
                if ($converterDef === null) {
                    $className = 'Null';
                } elseif (is_array($converterDef)) {
                    list($className) = array_keys($converterDef);
                    $parameter = $converterDef[$className];
                } else {
                    $className = $converterDef;
                }

                $this->converters[$columnName][] = new ConverterConfiguration($className, $parameter);
            }
        }

        foreach ($this->get('filters', array()) as $filter) {
            $columnName = $filter[0];
            $operator = $filter[1];
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

                $this->dependencies[] = $referencedTable;

                $this->filters[] = new DataDependentFilterConfiguration(array(
                    'columnName' => $columnName,
                    'referencedTable' => $referencedTable,
                    'referencedColumn' => $referencedColumn,
                ));
            } else {
                $this->filters[] = new FilterConfiguration(array(
                    'columnName' => $columnName,
                    'operator' => $operator,
                    'value' => $value,
                ));
            }
        }
    }
}
