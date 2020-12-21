<?php


namespace Digilist\SnakeDumper\Configuration\Table\Filter;


use InvalidArgumentException;

class FilterFactory
{
    public static function buildFilter(array $filter) {
        $operator = $filter[0];

        if (ColumnFilter::isValidOperator($operator)) {
            $columnName = $filter[1];
            $value = isset($filter[2]) ? $filter[2] : null;
            return new ColumnFilter($columnName, $operator, $value);
        }

        if (CompositeFilter::isValidOperator($operator)) {
            $subFilters = array_map([__CLASS__, 'buildFilter'], array_slice($filter,1));
            return new CompositeFilter($operator, $subFilters);
        }
        throw new InvalidArgumentException('Invalid filter operator: ' . $operator);
    }
}