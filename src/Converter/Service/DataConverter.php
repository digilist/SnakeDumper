<?php

namespace Digilist\SnakeDumper\Converter\Service;

use Digilist\SnakeDumper\Configuration\Table\ConverterDefinition;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\ChainConverter;
use Digilist\SnakeDumper\Converter\ConditionalConverter;
use Digilist\SnakeDumper\Converter\ConverterInterface;
use Digilist\SnakeDumper\Exception\InvalidConverterException;
use ReflectionClass;

class DataConverter implements DataConverterInterface
{

    /**
     * @var ConverterInterface[]
     */
    private $converters = array();

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($key, $value, array $context = array())
    {
        if (!array_key_exists($key, $this->converters)) {
            return $value;
        }

        return $this->converters[$key]->convert($value, $context);
    }

    /**
     * This function creates a single converter instance.
     *
     * @param ConverterDefinition $converterDefinition
     *
     * @throws InvalidConverterException
     * @return ConverterInterface
     */
    public static function createConverterInstance(ConverterDefinition $converterDefinition)
    {
        $class = $converterDefinition->getFullQualifiedClassName();
        if (!class_exists($class)) {
            $message = sprintf('The converter "%s" (%s) does not exist.', $converterDefinition->getClassName(), $class);
            throw new InvalidConverterException($message);
        }

        $parameters = $converterDefinition->getParameters();
        if ($parameters !== null) {
            return new $class($parameters);
        }

        return new $class();
    }

    /**
     * Adds a new converter for the specified key. If there is already a converter for this key,
     * a converter chain will be created.
     *
     * @param string $key
     * @param ConverterInterface $converter
     */
    protected function addConverter($key, ConverterInterface $converter, $allowChaining = false)
    {
        if (array_key_exists($key, $this->converters)) {
            if (!$allowChaining) {
                $message = sprintf(
                    'There is already a converter with key %s. Please provide a chain ' .
                    'if there are multiple converters or allow chaining.',
                    $key
                );
                throw new \InvalidArgumentException($message);
            }

            if ($this->converters[$key] instanceof ChainConverter) {
                $this->converters[$key]->addConverter($converter);
                return;
            }

            $converter = new ChainConverter([$converter]);
        }

        $this->converters[$key] = $converter;
    }
}
