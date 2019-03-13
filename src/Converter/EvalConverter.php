<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Exception\InvalidArgumentException;

/**
 * The EvalConverter lets you execute custom code to set your value
 *
 * @package Digilist\SnakeDumper\Converter
 */
class EvalConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $eval;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->eval = $parameters['eval'];

        if (empty($this->eval)) {
            throw new InvalidArgumentException('You have to pass the value to eval.');
        }
    }

    /**
     * @return string
     */
    public function getEval()
    {
        return $this->eval;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($value, array $context = array())
    {
        extract($context);
        $result = eval(sprintf('return (%s);', $this->eval));

        return $result;
    }
}

