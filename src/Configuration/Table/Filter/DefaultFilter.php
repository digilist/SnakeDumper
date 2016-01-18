<?php

namespace Digilist\SnakeDumper\Configuration\Table\Filter;

use Digilist\SnakeDumper\Configuration\AbstractConfiguration;
use InvalidArgumentException;

class DefaultFilter implements FilterInterface
{

    const OPERATOR_EQ = 'eq';
    const OPERATOR_NEQ = 'neq';
    const OPERATOR_LT = 'lt';
    const OPERATOR_LTE = 'lte';
    const OPERATOR_GT = 'gt';
    const OPERATOR_GTE = 'gte';
    const OPERATOR_IS_NULL = 'isNull';
    const OPERATOR_IS_NOT_NULL = 'isNotNull';
    const OPERATOR_LIKE = 'like';
    const OPERATOR_NOT_LIKE = 'notLike';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'notIn';

    /**
     * This array contains all valid operators.
     *
     * The operator is passed to the ExpressionBuilder of Doctrine,
     * therefore only the following operators are valid at the moment.
     *
     * The depends operator defines that the column's value must be one of a preselected value of another table.
     * For example: You dump all customers and their billings, but only the last 100 customers. Then you don't want to
     * dump all billings but only those, that belong to the dumped customers.
     * (so you define a filter "billing.customer_id depends customer.id")
     *
     * @var array
     */
    private static $validOperators = array(
        self::OPERATOR_EQ,
        self::OPERATOR_NEQ,
        self::OPERATOR_LT,
        self::OPERATOR_LTE,
        self::OPERATOR_GT,
        self::OPERATOR_GTE,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_NOT_NULL,
        self::OPERATOR_LIKE,
        self::OPERATOR_NOT_LIKE,
        self::OPERATOR_IN,
        self::OPERATOR_NOT_IN,
    );

    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $value;

    /**
     * DefaultFilter constructor.
     *
     * @param string $columnName
     * @param string $operator
     * @param string $value
     */
    public function __construct($columnName, $operator, $value = null)
    {
        if (!in_array($operator, self::$validOperators)) {
            throw new InvalidArgumentException('Invalid filter operator: ' . $operator);
        }

        $this->columnName = $columnName;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public function setColumnName($columnName)
    {
        return $this->columnName = $columnName;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     *
     * @return string
     */
    public function setOperator($operator)
    {
        return $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function setValue($value)
    {
        return $this->value = $value;
    }
}
