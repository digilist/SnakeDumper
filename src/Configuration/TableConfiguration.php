<?php

namespace Digilist\SnakeDumper\Configuration;

class TableConfiguration extends AbstractConfiguration
{

    private $name;

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
        return $this->get('ignore_content', boolval($value));
    }

    /**
     * @return ColumnConfiguration[]
     */
    public function getColumns()
    {
        $columns = array();
        foreach ($this->get('columns', array()) as $name => $column) {
            $columns[$name] = new ColumnConfiguration($name, $column);
        }

        return $columns;
    }

    protected function parseConfig(array $config)
    {
        // TODO: Implement parseConfig() method.
    }
}
