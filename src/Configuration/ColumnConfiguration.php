<?php

namespace Digilist\SnakeDumper\Configuration;

class ColumnConfiguration extends AbstractConfiguration
{

    private $name;

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->name = $name;
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ConverterConfiguration[]
     */
    public function getConverters()
    {
        $converters = array();
        foreach ($this->toArray() as $converterDef) {
            foreach ($converterDef as $className => $parameters) {
                $converters[] = new ConverterConfiguration($className, $parameters);
            }
        }

        return $converters;
    }

    protected function parseConfig(array $config)
    {
        // TODO: Implement parseConfig() method.
    }
}
