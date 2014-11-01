<?php

namespace Digilist\SnakeDumper\Converter;

class StringConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $string;

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * Convert the passed value into another value which does not contain sensible data any longer.
     *
     * @param string $value
     * @param array  $context
     *
     * @internal param string $key
     * @return string
     */
    public function convert($value, array $context = array())
    {
        return $this->string;
    }
}
