<?php

namespace Digilist\SnakeDumper\Configuration\Table;

class ConverterDefinition
{

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Creates a new converter configuration, from string or array.
     *
     * @param null|array|string $converterDef
     *
     * @return ConverterDefinition
     */
    public static function factory($converterDef)
    {
        $parameter = null;
        if ($converterDef === null) {
            $className = 'Null';
        } elseif (is_array($converterDef)) {
            list($className) = array_keys($converterDef);
            $parameter = $converterDef[$className];
        } else {
            $className = $converterDef;
        }

        return new ConverterDefinition($className, $parameter);
    }

    /**
     * @param string $className
     * @param mixed $parameters
     */
    public function __construct($className, $parameters = null)
    {
        $this->className = $className;
        $this->parameters = $parameters;
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
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
