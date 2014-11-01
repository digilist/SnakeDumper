<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\HumanNameHelper;

/**
 * The LastNameConverter replaces a value
 * with a random last name.
 */
class LastNameConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return HumanNameHelper::randomLastName();
    }
}
