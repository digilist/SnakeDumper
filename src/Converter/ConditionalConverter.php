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
        if (empty($parameters['if_true']) && empty($parameters['if_false'])) {
            throw new InvalidArgumentException('You have to pass either a if_true or if_false value.');
        }

        $this->condition = $parameters['condition'];
        if (isset($parameters['if_true'])) {
            $this->ifTrue = $parameters['if_true'];
        }
        if (isset($parameters['if_false'])) {
            $this->ifFalse = $parameters['if_false'];
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
