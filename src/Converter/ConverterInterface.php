<?php

namespace Digilist\SnakeDumper\Converter;

interface ConverterInterface
{

    /**
     * Convert the passed value into another value which does not contain sensible data any longer.
     *
     * @param string $table
     * @param string $column
     * @param string $value
     * @param array  $context
     *
     * @return mixed
     */
    public function convert($table, $column, $value, array $context = array());

} 
