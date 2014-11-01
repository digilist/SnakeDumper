<?php

namespace Digilist\SnakeDumper\Converter\ValueConverter;

class NullValueConverter implements ValueConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return null;
    }
}
