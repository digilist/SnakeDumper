<?php

namespace Digilist\SnakeDumper\Configuration;

interface ConfigurationInterface
{

    /**
     * @param array $config
     * @return void
     */
    public function fromArray(array $config);

    /**
     * @return array
     */
    public function toArray();
}
