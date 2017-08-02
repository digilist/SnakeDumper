<?php /** File containing class DatabaseConfiguration */

namespace Digilist\SnakeDumper\Configuration;

use Digilist\SnakeDumper\Exception\ConfigurationException;
use Doctrine\DBAL\Connection;

class DatabaseConfiguration extends AbstractConfiguration
{

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->get('dbname');
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
        return $this->get('driver');
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
        return $this->get('host');
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
    public function getPort()
    {
        return $this->get('port');
    }

    /**
     * @param string $value
     * @return string
     */
    public function setPort($value)
    {
        return $this->set('port', $value);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->get('password');
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
        return $this->get('user');
    }

    /**
     * @param string $value
     * @return string
     */
    public function setUser($value)
    {
        return $this->set('user', $value);
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->get('charset');
    }

    /**
     * @param string $value
     * @return string
     */
    public function setCharset($value)
    {
        return $this->set('charset', $value);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->get('connection');
    }

    /**
     * @return array
     */
    public function getCustomTypes()
    {
        return $this->get('custom_types');
    }

    protected function parseConfig(array $config)
    {
        if (!isset($config['connection'])) {
            $this->ensureHas('dbname');
            $this->ensureHas('driver');
            $this->ensureHas('host');
            $this->ensureHas('password');
            $this->ensureHas('user');
            $this->ensureHas('charset');

            if (!isset($config['custom_types'])) {
                $config['custom_types'] = array();
            }

            foreach ($config['custom_types'] as $customType) {
                if (!isset($customType['class'])) {
                    throw ConfigurationException::createEnsureHasException('class');
                }
            }
        }
    }
}
