<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The FirstNameConverter replaces a value
 * with a random first name.
 */
class FirstNameConverter extends FakerConverter
{

    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'firstName';

        parent::__construct($arguments);
    }
}
