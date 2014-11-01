<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\VariableParserHelper;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class AppendConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $append;

    /**
     * @param string $append
     */
    public function __construct($append)
    {
        $this->append = $append;

        if (empty($this->append)) {
            throw new InvalidArgumentException('You have to pass the value to append.');
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
        return $value . VariableParserHelper::parse($this->append, $context);
    }
}
