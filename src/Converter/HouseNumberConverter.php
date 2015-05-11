<?php

namespace Digilist\SnakeDumper\Converter;

class HouseNumberConverter extends FakerConverter
{

    public function __construct(array $arguments = array())
    {
        $arguments['formatter'] = 'buildingNumber';

        parent::__construct($arguments);
    }
}
