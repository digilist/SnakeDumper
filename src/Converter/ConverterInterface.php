<?php

namespace Digilist\SnakeDumper\Converter;

interface ConverterInterface
{

    /**
     * Convert the passed value into another value which does not contain sensible data any longer.
     *
     * @param string $value
     *
     * @return string
     */
    public function convert($value);

} 
