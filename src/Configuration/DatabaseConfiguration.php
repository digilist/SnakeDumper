<?php /** File containing class DatabaseConfiguration */

namespace Digilist\SnakeDumper\Configuration;

class DatabaseConfiguration extends AbstractConfiguration
{

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->get('dbname', null);
    }

    /**
     * @param string $value
     * @return string
     */
    public function setDatabaseName($value)
    {
        return $this->set('dbname', $value);
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->get('driver', null);
    }

    /**
     * @param string $value
     * @return string
     */
    public function setDriver($value)
    {
        return $this->set('driver', $value);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->get('host', null);
    }

    /**
     * @param string $value
     * @return string
     */
    public function setHost($value)
    {
        return $this->set('host', $value);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->get('password', null);
    }

    /**
     * @param string $value
     * @return string
     */
    public function setPassword($value)
    {
        return $this->set('password', $value);
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->get('user', null);
    }

    /**
     * @param string $value
     * @return string
     */
    public function setUser($value)
    {
        return $this->set('user', $value);
    }

    protected function parseConfig(array $config)
    {
        $this->ensureHas('dbname');
        $this->ensureHas('driver');
        $this->ensureHas('host');
        $this->ensureHas('password');
        $this->ensureHas('user');
    }
}
