<?php

namespace Digilist\SnakeDumper\Converter;

use Digilist\SnakeDumper\Exception\InvalidArgumentException;

class ConcatConverter implements ConverterInterface
{

    /**
     * @var string
     */
    private $prepend;

    /**
     * @var string
     */
    private $append;

    public function __construct(array $parameters)
    {
        if (isset($parameters['prepend'])) {
            $this->prepend = $parameters['prepend'];
        }
        if (isset($parameters['append'])) {
            $this->append = $parameters['append'];
        }

        if (empty($this->prepend) && empty($this->append)) {
            throw new InvalidArgumentException('You have to pass either an append or prepend value.');
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
        return $this->prepend . $value . $this->append;
    }
}
