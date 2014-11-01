<?php /** File containing class AbstractConfiguration */

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Exception\ConfigurationException;

/**
 * @package Configuration
 * @author moellers
 */
abstract class AbstractConfiguration implements ConfigurationInterface, CommonConfigurationInterface
{

    private $config;
    private $nodeName;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->fromArray($config);
        }
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
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->config[$key];
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

    abstract protected function parseConfig(array $config);
}
