<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\HouseNumberHelper;
use Digilist\SnakeDumper\Converter\Helper\StreetNameHelper;

/**
 * The StreetNameConverter replaces a value
 * with a random street name.
 */
class StreetNameConverter implements ConverterInterface
{

    /**
     * @var bool
     */
    private $includeNumber = false;

    /**
     * @param array|bool $parameter
     */
    public function __construct($parameter)
    {
        if (is_bool($parameter)) {
            $this->includeNumber = $parameter;
        } else {
            if (isset($parameter['number'])) {
                $this->includeNumber = (bool) $parameter['number'];
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        $street = StreetNameHelper::randomStreetName();

        if ($this->includeNumber) {
            $street .= ' ' . HouseNumberHelper::generateNumber();
        }

        return $street;
    }
}
