<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;
use InvalidArgumentException;

/**
 * The LoremIpsumConverter replaces a text with lorem ipsum.
 */
class LoremConverter extends FakerConverter
{

    /**
     * @param int|array $sentences
     *
     * @throws InvalidArgumentException
     */
    public function __construct($sentences = null)
    {
        if (empty($sentences) || !is_int($sentences)) {
            throw new InvalidArgumentException('You have to pass the number of sentences.');
        }

        $parameters = [];
        $parameters['formatter'] = 'sentence';
        $parameters['arguments'] = array(
            'nbSentences' => $sentences,
        );

        parent::__construct($parameters);
    }
}
