<?php

namespace Digilist\SnakeDumper\Converter\Service;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\ChainConverter;
use Digilist\SnakeDumper\Converter\ConverterInterface;

abstract class ConverterService implements ConverterServiceInterface
{

    /**
     * @var ConverterInterface[]
     */
    private $converter = array();

    /**
     * @var DumperConfigurationInterface
     */
    protected $config;

    /**
     * @param DumperConfigurationInterface $config
     */
    public function __construct(DumperConfigurationInterface $config)
    {
        $this->config = $config;

        $this->initConverters();
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
        if (!array_key_exists($key, $this->converter)) {
            return $value;
        }

        return $this->converter[$key]->convert($value, $context);
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
        if (array_key_exists($key, $this->converter)) {
            $message = sprintf(
                'There is already a converter with key %s. Please provide a chain if there are multiple converter.',
                $key
            );
            throw new \InvalidArgumentException($message);
        }

        $this->converter[$key] = $converter;
    }

    /**
     * Read the configuration and create all necessary converter
     *
     */
    protected abstract function initConverters();
}
