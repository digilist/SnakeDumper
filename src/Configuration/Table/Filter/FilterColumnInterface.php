<?php
namespace Digilist\SnakeDumper\Configuration\Table\Filter;

interface FilterColumnInterface extends FilterInterface
{

    /**
     * @return string
     */
    public function getColumnName();


    /**
     * @return string
     */
    public function getValue();
}
