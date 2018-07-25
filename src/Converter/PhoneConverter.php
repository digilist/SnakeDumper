<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The PhoneConverter replaces a value
 * with a random phone.
 */
class PhoneConverter extends FakerConverter
{
    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'phone';

        parent::__construct($arguments);
    }

    public function convert($value, array $context = array())
    {
        return rand(1111111111, 9999999999);
    }
}
