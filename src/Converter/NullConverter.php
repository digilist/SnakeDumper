<?php

namespace Digilist\SnakeDumper\Converter;

class NullConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return null;
    }
}
