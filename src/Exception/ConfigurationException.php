<?php

namespace Digilist\SnakeDumper\Exception;

class ConfigurationException extends \Exception
{

    public final static function createEnsureHasException($key)
    {
        return new ConfigurationException('The configuration has to have a key `' . $key . '`.');
    }
}
