<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;

class DataDependentFilterConfiguration extends StandardFilterConfiguration
{

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->get('table');
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        return $this->set('table', $table);
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->get('column');
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function setColumn($column)
    {
        return $this->set('column', $column);
    }

    protected function parseConfig(array $config)
    {
        $this->setOperator('in');
        $this->setValue(array());
    }
}
