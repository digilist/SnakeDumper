<?php

namespace Digilist\SnakeDumper\Configuration;

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
        // parse columns
        foreach ($this->get('columns', array()) as $name => $column) {
            $this->columns[$name] = new ColumnConfiguration($name, $column);
        }
    }
}
