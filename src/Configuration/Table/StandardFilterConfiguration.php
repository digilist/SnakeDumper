<?php

namespace Digilist\SnakeDumper\Configuration\Table;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;

class StandardFilterConfiguration extends AbstractConfiguration
{

    /**
     * Array which contains all valid operators.
     *
     * The operator is passed to the ExpressionBuilder of Doctrine,
     * therefore only the following are valid at the moment.
     *
     * The depends operator defines that the column's value must be one of a preselected value of another table.
     * For example: You dump all Customers and their billings, but only the last 100 Customers. Then you don't want to
     * dump all billings, so you define a filter "Billing.customer_id depends Customer.id"
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
     * @param mixed $value
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
