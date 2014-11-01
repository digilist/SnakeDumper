<?php /** File containing class SnakeConfiguration */

namespace Digilist\SnakeDumper\Configuration;

/**
 * @package Configuration
 * @author moellers
 */
class SnakeConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
{

    private $database;

    protected function parseConfig(array $oldConfig, array $newConfig)
    {
        $this->database = new DatabaseConfiguration($this->get('database'));
    }

    /**
     * @return DatabaseConfigurationInterface
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getSettings()
    {
        // TODO: Implement getSettings() method.
    }

    /**
     * @return ConfigurationInterface
     */
    public function getAnonymize()
    {
        // TODO: Implement getAnonymize() method.
    }
}
