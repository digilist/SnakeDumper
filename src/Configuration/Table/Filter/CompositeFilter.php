<?php


namespace Digilist\SnakeDumper\Configuration\Table\Filter;


use InvalidArgumentException;

class CompositeFilter implements FilterInterface
{
    const OPERATOR_OR = 'or';
    const OPERATOR_AND = 'and';

    private static $validOperators = [
        self::OPERATOR_OR,
        self::OPERATOR_AND,
    ];

    private static $dbalOperators = [
        self::OPERATOR_OR => 'orX',
        self::OPERATOR_AND => 'andX'
    ];

    /**
     * @var array
     */
    private $filters;

    /**
     * @var string
     */
    private $operator;


    public function __construct($operator, array $filters = [])
    {
        if (!self::isValidOperator($operator)) {
            throw new InvalidArgumentException('Invalid filter operator: ' . $operator);
        }

        $this->operator = $operator;
        $this->filters = $filters;
    }

    /**
     * @param $operator
     * @return bool
     */
    public static function isValidOperator($operator)
    {
        return in_array($operator, self::$validOperators);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return self::$dbalOperators[$this->operator];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }
}