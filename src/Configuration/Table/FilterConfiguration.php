<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;

class FilterConfiguration extends AbstractConfiguration
{

    /**
     * Array which contains all valid operators.
     *
     * The operator is passed to the ExpressionBuilder of Doctrine,
     * therefore only the following are valid at the moment.
     *
     * @var array
     */
    private static $validOperators = array(
        'eq',
        'neq',
        'lt',
        'lte',
        'gt',
        'gte',
        'isNull',
        'isNotNull',
        'like',
        'notLike',
        'in',
        'notIn',
    );

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

    protected function parseConfig(array $config)
    {
        if (!in_array($this->getOperator(), self::$validOperators)) {
            throw new \InvalidArgumentException('Invalid filter operator: ' . $this->getOperator());
        }
    }

}
