<?php

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Exception\ConfigurationException;

abstract class AbstractConfiguration
{

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->fromArray($config);
            return;
        }

        $this->config = $config;
    }

    /**
     * @param array $config
     * @return void
     */
    public function fromArray(array $config)
    {
        $this->config = $config;

        $this->parseConfig($config);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set($key, $value)
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->config[$key]);
    }

    /**
     * @param string $key
     * @throws ConfigurationException
     */
    protected function ensureHas($key)
    {
        if (!$this->has($key)) {
            throw ConfigurationException::createEnsureHasException($key);
        }
    }

    /**
     * Override to allow additional config parsing.
     *
     * @param array $config
     */
    protected function parseConfig(array $config)
    {
    }
}
