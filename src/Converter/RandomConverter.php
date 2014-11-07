<?php

namespace Digilist\SnakeDumper\Converter;

/**
 * The RandomConverter inserts random numbers.
 *
 * @package Digilist\SnakeDumper\Converter
 */
class RandomConverter implements ConverterInterface
{

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->min = 0;
        $this->max = getrandmax();

        // Sets minimum random int parameter
        if (isset($parameters['min'])) {
            $this->min = intval($parameters['min']);
        }

        // Sets maximum random int parameter
        if (isset($parameters['max'])) {
            $this->max = intval($parameters['max']);
        }

        // Swap min and max if max < min
        if ($this->min > $this->max) {
            $max = $this->max;
            $this->max = $this->min;
            $this->min = $max;
        }
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($value, array $context = array())
    {
        return rand($this->min, $this->max);
    }
}
