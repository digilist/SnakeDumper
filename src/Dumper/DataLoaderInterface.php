<?php


namespace Digilist\SnakeDumper\Dumper;


interface DataLoaderInterface
{
    /**
     * Get distinct values from a table and column
     *
     * @param string $table
     * @param string $property
     * @return array
     */
    public function getDistinctValues($table, $property);
}