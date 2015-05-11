<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The CompanyNameConverter replaces a value with a random company name.
 */
class CompanyNameConverter extends FakerConverter
{

    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'company';

        parent::__construct($arguments);
    }
}
