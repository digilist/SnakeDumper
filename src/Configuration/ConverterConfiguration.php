<?php

namespace Digilist\SnakeDumper\Configuration;

class ConverterConfiguration extends AbstractConfiguration
{

    private $className;

    /**
     * @param string $className
     * @param array $parameters
     */
    public function __construct($className, array $parameters = null)
    {
        $this->className = $className;
        parent::__construct((array) $parameters);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFullQualifiedClassName()
    {
        if (strpos($this->className, '\\') !== false) {
            return $this->className;
        }

        return "Digilist\\SnakeDumper\\Converter\\{$this->className}Converter";
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClassName($class)
    {
        $this->className = $class;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->toArray();
    }

    protected function parseConfig(array $config)
    {
        // TODO: Implement parseConfig() method.
    }
}
