<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Converter\Helper\VariableParserHelper;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class ConditionalConverter implements ConverterInterface
{

    const UNDEFINED = '_____undefined_____';

    /**
     * @var string
     */
    private $condition;

    /**
     * @var string
     */
    private $ifTrue = self::UNDEFINED;

    /**
     * @var string
     */
    private $ifFalse = self::UNDEFINED;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['condition'])) {
            throw new InvalidArgumentException('You have to pass a condition');
        }
        if (empty($parameters['ifTrue']) && empty($parameters['ifFalse'])) {
            throw new InvalidArgumentException('You have to pass either a ifTrue or ifFalse value.');
        }

        $this->condition = $parameters['condition'];
        if (isset($parameters['ifTrue'])) {
            $this->ifTrue = $parameters['ifTrue'];
        }
        if (isset($parameters['ifFalse'])) {
            $this->ifFalse = $parameters['ifFalse'];
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
        extract($context);
        $result = eval(sprintf('return (%s);', $this->condition));

        if ($result && $this->ifTrue !== self::UNDEFINED) {
            return VariableParserHelper::parse($this->ifTrue, $context);
        }
        if (!$result && $this->ifFalse !== self::UNDEFINED) {
            return VariableParserHelper::parse($this->ifFalse, $context);
        }

        return $value;
    }
}
