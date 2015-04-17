<?php

namespace Digilist\SnakeDumper\Configuration;

class FilterConfiguration extends AbstractConfiguration
{

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->get('operator');
    }

    /**
     * @param string $operator
     *
     * @return string
     */
    public function setOperator($operator)
    {
        return $this->set('operator', $operator);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->get('value');
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function setValue($value)
    {
        return $this->set('value', $value);
    }
}
