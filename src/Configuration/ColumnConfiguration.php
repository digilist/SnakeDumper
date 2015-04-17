<?php

namespace Digilist\SnakeDumper\Configuration;

class ColumnConfiguration extends AbstractConfiguration
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var ConverterConfiguration[]
     */
    private $converters;

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
        return $this->converters;
    }

    /**
     * @param array $config
     */
    protected function parseConfig(array $config)
    {
        $this->parseConverters();
    }

    /**
     *
     */
    private function parseConverters()
    {
        foreach ($this->get('converters') as $converterDef) {

            $parameter = null;
            if ($converterDef === null) {
                $className = 'Null';
            } elseif (is_array($converterDef)) {
                list($className) = array_keys($converterDef);
                $parameter = $converterDef[$className];
            } else {
                $className = $converterDef;
            }

            $this->converters[] = new ConverterConfiguration($className, $parameter);
        }
    }
}
