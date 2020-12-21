<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;
use Digilist\SnakeDumper\Configuration\Table\Filter\CompositeFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\FilterFactory;
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
     * @var TableDependencyConstraint[]
     */
    private $dependentTables = [];

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
     * @return bool
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
     * @param TableDependencyConstraint $dependency
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
        return array_merge($this->filters, $this->getFiltersFromDependencies());
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
     * @param string $collectColumn
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
        if (!isset($config['dependencies'])) {
            $config['dependencies'] = [];
        }


        // Parse converter definitions and create objects
        foreach ($config['converters'] as $columnName => $converters) {
            foreach ($converters as $converterDef) {
                $this->convertersDefinitions[$columnName][] = ConverterDefinition::factory($converterDef);
            }
        }

        // Parse filter configurations and create filter objects
        foreach ($config['filters'] as $filterConfig) {
            $dependency = TableDependencyConstraint::createFromFilterConfig($filterConfig);
            if(!is_null($dependency)) {
                $this->addDependency($dependency);
                continue;
            }

            $filter = FilterFactory::buildFilter($filterConfig);
            $this->filters[] = $filter;
        }


        foreach ($config['dependencies'] as $dataDependency) {
            $this->addDependency(new TableDependencyConstraint(
                $dataDependency['referenced_table'],
                $dataDependency['referenced_column'],
                $dataDependency['column'],
                $dataDependency['condition']
            ));
        }
    }

    /**
     *
     * @return array
     */
    private function getFiltersFromDependencies()
    {
        if (count($this->getDependentTables()) == 0) {
            return [];
        }

        $dependenciesPerColumn = array_reduce($this->getDependentTables(), function ($acc, TableDependencyConstraint $dependency) {
            if (!isset($acc[$dependency->getColumn()])) {
                $acc[$dependency->getColumn()] = [];
            }
            $acc[$dependency->getColumn()][] = $dependency;
            return $acc;
        }, []);


        return array_map(function ($dependencies) {
            if (count($dependencies) > 1) {
                $dependencyFilters = array_map(function (TableDependencyConstraint $dependency) {
                    return $dependency->getFilter();
                }, $dependencies);
                return new CompositeFilter(CompositeFilter::OPERATOR_OR, $dependencyFilters);
            }
            $dependency = $dependencies[0];
            return $dependency->getFilter();
        }, $dependenciesPerColumn);
    }


}
