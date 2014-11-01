<?php /** File containing class DatabaseConfiguration */

namespace Digilist\SnakeDumper\Configuration;

class DatabaseConfiguration extends AbstractConfiguration implements DatabaseConfigurationInterface
{

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->get('dbname');
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->get('driver');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->get('host');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->get('password');
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->get('user');
    }

    protected function parseConfig(array $oldConfig, array $newConfig)
    {
        $this->ensureHas('dbname');
        $this->ensureHas('driver');
        $this->ensureHas('host');
        $this->ensureHas('password');
        $this->ensureHas('user');
    }
}
