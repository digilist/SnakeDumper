<?php

namespace Digilist\SnakeDumper\Converter;

class ChainConverter implements ConverterInterface
{

    /**
     * @var ConverterInterface[]
     */
    private $converter;

    /**
     * @param ConverterInterface[] $converter
     */
    public function __construct(array $converter = array())
    {
        $this->converter = $converter;
    }

    /**
     * Adds a converter into the chain.
     *
     * @param ConverterInterface $converter
     *
     * @return $this
     */
    public function addConverter(ConverterInterface $converter)
    {
        $this->converter[] = $converter;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($value, array $context = array())
    {
        foreach ($this->converter as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
