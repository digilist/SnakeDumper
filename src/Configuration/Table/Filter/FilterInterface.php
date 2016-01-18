<?php
namespace Digilist\SnakeDumper\Configuration\Table\Filter;

interface FilterInterface
{

    /**
     * @return string
     */
    public function getColumnName();

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return string
     */
    public function getValue();
}
