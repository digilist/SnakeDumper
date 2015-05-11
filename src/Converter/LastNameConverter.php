<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The LastNameConverter replaces a value
 * with a random last name.
 */
class LastNameConverter extends FakerConverter
{

    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'lastName';

        parent::__construct($arguments);
    }
}
