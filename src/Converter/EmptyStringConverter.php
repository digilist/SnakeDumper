<?php

namespace Digilist\SnakeDumper\Converter;

class EmptyStringConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return '';
    }
}
