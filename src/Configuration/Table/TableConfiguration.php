<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;
use Digilist\SnakeDumper\Configuration\Table\ColumnConfiguration;

class TableConfiguration extends AbstractConfiguration
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var ColumnConfiguration[]
     */
    private $columns = array();

    /**
     * Contains the names of all dependent tables
     *
     * @var array
     */
    private $dependencies = array();

    /**
     * Contains the names of all columns which values should be collected for later reuse.
     *
     * @var array
     */
    private $collectColumns = array();

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
     * @return ColumnConfiguration[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn($name)
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * @param string $name
     *
     * @return ColumnConfiguration
     */
    public function getColumn($name)
    {
        return $this->columns[$name];
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
    public function getCollectColumns()
    {
        return $this->collectColumns;
    }

    /**
     * @param array $collectColumns
     *
     * @return $this
     */
    public function setCollectColumns($collectColumns)
    {
        $this->collectColumns = array_unique($collectColumns);

        return $this;
    }

    /**
     * @param array $collectColumn
     *
     * @return $this
     */
    public function addCollectColumn($collectColumn)
    {
        $this->collectColumns[] = $collectColumn;
        $this->collectColumns = array_unique($this->collectColumns);

        return $this;
    }

    protected function parseConfig(array $config)
    {
        $columns = array();

        foreach ($this->get('converters', array()) as $columnName => $converters) {
            $columns[$columnName] = $this->createInitialColumnArray();
            $columns[$columnName]['converters'] = $converters;
        }

        foreach ($this->get('filters', array()) as $filter) {
            $columnName = $filter[0];
            $operator = $filter[1];
            $value = $filter[2];

            if (!array_key_exists($columnName, $columns)) {
                $columns[$columnName] = $this->createInitialColumnArray();
            }

            if ($operator == 'depends') {
                $referencedColumn = explode('.', $value);
                if (count($referencedColumn) !== 2) {
                    throw new \InvalidArgumentException(
                        'Unexpected format for depends operator "' . $value . '". Expected format "table.column"'
                    );
                }

                $table = $referencedColumn[0];
                $column = $referencedColumn[1];

                $this->dependencies[] = $table;
                $columns[$columnName]['filters'][] = array(
                    'operator' => 'depends',
                    'table' => $table,
                    'column' => $column,
                );
            } else {
                $columns[$columnName]['filters'][] = array(
                    'operator' => $operator,
                    'value' => $value,
                );
            }
        }

        foreach ($columns as $columnName => $config) {
            $this->columns[$columnName] = new ColumnConfiguration($columnName, $config);
        }
    }

    private function createInitialColumnArray()
    {
        return array(
            'converters' => array(),
            'filters' => array(),
        );
    }
}
