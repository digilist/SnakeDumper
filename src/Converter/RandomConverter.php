<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Exception\InvalidArgumentException;

/**
 * The RandomConverter inserts random numbers.
 *
 * @package Digilist\SnakeDumper\Converter
 */
class RandomConverter implements ConverterInterface
{

    /**
     * @var float
     */
    private $min;

    /**
     * @var float
     */
    private $max;

    /**
     * @var float|null
     */
    private $step;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->min = 0.0;
        $this->max = mt_getrandmax();

        // Sets minimum random int parameter
        if (isset($parameters['min'])) {
            $this->min = floatval($parameters['min']);
        }

        // Sets maximum random int parameter
        if (isset($parameters['max'])) {
            $this->max = floatval($parameters['max']);
        }

        // Sets step in which random numbers occur
        if (isset($parameters['step'])) {
            $this->step = floatval($parameters['step']);

            if ($this->step <= 0) {
                throw new InvalidArgumentException('Step must be greater than zero');
            }
        }

        // Swap min and max if max < min
        if ($this->min > $this->max) {
            $max = $this->max;
            $this->max = $this->min;
            $this->min = $max;
        }
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return float|null
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($value, array $context = array())
    {
        if (null === $this->step) {
            return mt_rand($this->min, $this->max);
        }

        return round(mt_rand($this->min, $this->max) / $this->step) * $this->step;
    }
}
