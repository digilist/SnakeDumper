<?php

namespace Digilist\SnakeDumper\Converter;

/**
 * The StreetNameConverter replaces a value with a random street name.
 */
class StreetNameConverter extends FakerConverter
{

    /**
     * If $parameters is a bool it says to include or not to include the street number
     *
     * @param array|bool $parameters
     */
    public function __construct($parameters = false)
    {
        if (is_bool($parameters)) {
            $parameters = array(
                'number' => $parameters,
            );
        } else if (!isset($parameters['number'])) {
            $parameters['number'] = false;
        }

        $parameters = array_merge(array(
            'formatter' => $parameters['number'] ? 'streetAddress' : 'streetName',
        ), $parameters);

        parent::__construct($parameters);
    }
}
