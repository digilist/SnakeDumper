<?php /** File containing class ConfigurationException */

namespace Digilist\SnakeDumper\Exception;

/**
 * @package Exception
 * @author moellers
 */
class ConfigurationException extends \Exception
{

    public final static function createEnsureHasException($key)
    {
        return new ConfigurationException('The configuration has to have a key `' . $key . '`.');
    }
}
