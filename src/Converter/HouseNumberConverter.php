<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\HouseNumberHelper;

class HouseNumberConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return HouseNumberHelper::generateNumber();
    }
}
