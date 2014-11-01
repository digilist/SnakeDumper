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
     * @return mixed
     */
    public function get($key);
}
