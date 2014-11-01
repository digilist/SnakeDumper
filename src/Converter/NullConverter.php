<?php

namespace Digilist\SnakeDumper\Converter;

class NullConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value)
    {
        return null;
    }
}
