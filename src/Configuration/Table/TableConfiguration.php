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
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config)
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
     * @param bool $value
     * @return $this
     */
    public function setIgnoreTable($value)
    {
        return $this->set('ignore_table', (bool) $value);
    }

    /**
     * @return bool
     */
    public function isContentIgnored()
    {
        return $this->get('ignore_content', false);
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIgnoreContent($value)
    {
        return $this->set('ignore_content', boolval($value));
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

            $columns[$columnName]['filters'][] = array(
                'operator' => $operator,
                'value' => $value,
            );
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
