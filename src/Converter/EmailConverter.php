<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The EmailConverter replaces a value
 * with a random email.
 */
class EmailConverter extends FakerConverter
{

    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'email';

        parent::__construct($arguments);
    }

    public function convert($value, array $context = array())
    {
        return $context[id]."@fakemail.com";
    }
}
