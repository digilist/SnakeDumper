<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\VariableParserHelper;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class ReplaceConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $replace;

    /**
     * @param array $parameters
     *
     * @internal param string $replace
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['search']) || empty($parameters['replace'])) {
            throw new InvalidArgumentException('You have to pass the search and value to replace.');
        }

        $this->search = $parameters['search'];
        $this->replace = $parameters['replace'];

    }

    /**
     * Convert the passed value into another value which does not contain sensible data any longer.
     *
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($value, array $context = array())
    {
        return str_replace($this->search, VariableParserHelper::parse($this->replace, $context), $value);
    }
}
