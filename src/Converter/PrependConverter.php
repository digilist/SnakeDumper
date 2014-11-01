<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\VariableParserHelper;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class PrependConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $prepend;

    /**
     * @param string $prepend
     */
    public function __construct($prepend)
    {
        $this->prepend = $prepend;

        if (empty($this->prepend)) {
            throw new InvalidArgumentException('You have to pass the value to prepend.');
        }
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
        return VariableParserHelper::parse($this->prepend, $context) . $value;
    }
}
