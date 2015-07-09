<?php

namespace Digilist\SnakeDumper\Converter\Service;

use Digilist\SnakeDumper\Configuration\Table\ConverterConfiguration;
use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\ChainConverter;
use Digilist\SnakeDumper\Converter\ConditionalConverter;
use Digilist\SnakeDumper\Converter\ConverterInterface;
use Digilist\SnakeDumper\Exception\InvalidConverterException;
use ReflectionClass;

class ConverterService implements ConverterServiceInterface
{

    /**
     * @var ConverterInterface[]
     */
    private $converters = array();

    /**
     */
    public function __construct()
    {
    }

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
     * This function creates the (chain) converter, according to the passed configurations.
     *
     * @param ConverterConfiguration[] $converterConfigurations
     *
     * @return ChainConverter|ConverterInterface|null
     * @throws InvalidConverterException
     */
    public static function createConverterFromConfig(array $converterConfigurations)
    {
        $chainConverter = null;
        if (count($converterConfigurations) > 1) {
            $chainConverter = new ChainConverter();

            foreach ($converterConfigurations as $converterConf) {
                $converter = self::createConverterInstance($converterConf);
                $chainConverter->addConverter($converter);
            }

            return $chainConverter;
        }

        return self::createConverterInstance($converterConfigurations[0]);
    }

    /**
     * Adds a new converter for the specified key. If there is already a converter for this key,
     * a converter chain will be created.
     *
     * @param string $key
     * @param ConverterInterface $converter
     */
    protected function addConverter($key, ConverterInterface $converter)
    {
        if (array_key_exists($key, $this->converters)) {
            $message = sprintf(
                'There is already a converter with key %s. Please provide a chain if there are multiple converters.',
                $key
            );
            throw new \InvalidArgumentException($message);
        }

        $this->converters[$key] = $converter;
    }

    /**
     * This method helps to add the different converters.
     * If there is only one converter, it will be added directly.
     * If there are multiple converters they will be combined into a chain.
     *
     * @param string                   $key
     * @param ConverterConfiguration[] $converterConfigurations
     */
    protected function addConvertersFromConfig($key, array $converterConfigurations)
    {
        if (count($converterConfigurations) == 0) {
            return;
        }

        $converter = $this->createConverterFromConfig($converterConfigurations);
        $this->addConverter($key, $converter);
    }

    /**
     * This function creates a single converter instance.
     *
     * @param ConverterConfiguration $converterConf
     *
     * @throws InvalidConverterException
     * @return ConverterInterface
     */
    protected static function createConverterInstance(ConverterConfiguration $converterConf)
    {
        $class = $converterConf->getFullQualifiedClassName();
        if (!class_exists($class)) {
            $message = sprintf('The converter "%s" (%s) does not exist.', $converterConf->getClassName(), $class);
            throw new InvalidConverterException($message);
        }

        $parameters = $converterConf->getParameters();
        if ($parameters !== null) {
            return new $class($parameters);
        }

        return new $class();
    }
}
