<?php

namespace Digilist\SnakeDumper\Configuration;

interface CommonConfigurationInterface
{
    /**
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set($key, $value);
}
