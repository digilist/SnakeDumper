<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\HumanNameHelper;

/**
 * The JohnDoeConverter replaces a value
 * with a random first and last name.
 */
class JohnDoeConverter implements ConverterInterface
{

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        $firstName = HumanNameHelper::randomFirstName();
        $lastName = HumanNameHelper::randomLastName();

        return "$firstName $lastName";
    }
}
