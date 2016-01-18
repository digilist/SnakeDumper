<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Configuration\Table\ConverterDefinition;
use Digilist\SnakeDumper\Converter\Helper\VariableParserHelper;
use Digilist\SnakeDumper\Converter\Service\ConverterService;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class ConditionalConverter implements ConverterInterface
{

    // This undefined constant is used, as null, false etc. are also valid values.
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
     * @var ConverterInterface
     */
    private $ifTrueConverter;

    /**
     * @var ConverterInterface
     */
    private $ifFalseConverter;

    /**
     * @param array                     $parameters
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['condition'])) {
            throw new InvalidArgumentException('You have to pass a condition');
        }
        if (empty($parameters['if_true']) && empty($parameters['if_false']) &&
                empty($parameters['if_true_converters']) && empty($parameters['if_false_converters'])) {
            throw new InvalidArgumentException('You have to pass either a if_true or if_false value (or _converters).');
        }

        if (!empty($parameters['if_true']) && !empty($parameters['if_true_converters'])) {
            throw new InvalidArgumentException('You cannot pass both the if_true and if_true_converters parameters');
        }
        if (!empty($parameters['if_false']) && !empty($parameters['if_false_converters'])) {
            throw new InvalidArgumentException('You cannot pass both the if_false and if_false_converters parameters');
        }

        $this->condition = $parameters['condition'];
        if (isset($parameters['if_true'])) {
            $this->ifTrue = $parameters['if_true'];
        } elseif (!empty($parameters['if_true_converters'])) {
            $this->ifTrueConverter = $this->createConditionalConverter($parameters['if_true_converters']);
        }

        if (isset($parameters['if_false'])) {
            $this->ifFalse = $parameters['if_false'];
        } elseif (!empty($parameters['if_false_converters'])) {
            $this->ifFalseConverter = $this->createConditionalConverter($parameters['if_false_converters']);
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

        if ($result && $this->ifTrueConverter !== null) {
            return $this->ifTrueConverter->convert($value, $context);
        }
        if (!$result && $this->ifFalseConverter !== null) {
            return $this->ifFalseConverter->convert($value, $context);
        }

        return $value;
    }

    /**
     * @param array $converterDefinitions
     *
     * @return ChainConverter
     */
    private function createConditionalConverter($converterDefinitions)
    {
        $converters = [];
        foreach ($converterDefinitions as $definition) {
            $converterDefinition = ConverterDefinition::factory($definition);
            $converters[] = ConverterService::createConverterInstance($converterDefinition);
        }

        if (count($converters) > 1) {
            $converter = new ChainConverter($converters);
        } else {
            $converter = $converters[0];
        }

        return $converter;
    }
}
