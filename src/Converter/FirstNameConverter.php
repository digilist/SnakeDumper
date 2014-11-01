<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\HumanNameHelper;

/**
 * The FirstNameConverter replaces a value
 * with a random first name.
 */
class FirstNameConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return HumanNameHelper::randomFirstName();
    }
}
