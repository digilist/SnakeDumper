<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\ValueConverter\ValueConverterInterface;

class Converter implements ConverterInterface
{

    /**
     * @var ValueConverterInterface[]
     */
    private $valueConverter;

    public function __construct()
    {
        $this->valueConverter = [];
    }

    public function convert($table, $column, $value, array $context = [])
    {
        foreach ($this->valueConverter as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
