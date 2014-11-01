<?php /** File containing class SnakeConfiguration */

namespace Digilist\SnakeDumper\Configuration;

/**
 * @package Configuration
 * @author moellers
 */
class SnakeConfiguration extends AbstractConfiguration implements DumperConfigurationInterface
{

    private $database;
    private $output;

    /**
     * @return DatabaseConfiguration
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getTables()
    {
        // TODO: Implement getAnonymize() method.
    }

    /**
     * @return string
     */
    public function getDumper()
    {
        return $this->get('dumper', null);
    }

    /**
     * @return string
     */
    public function getFullQualifiedDumper()
    {
        return 'Digilist\\SnakeDumper\\Dumper\\' . $this->getDumper() . 'Dumper';
    }

    /**
     * @return OutputConfiguration
     */
    public function getOutput()
    {
        return $this->output;
    }

    protected function parseConfig(array $config)
    {
        $this->database = new DatabaseConfiguration($this->get('database', null));
        $this->output = new OutputConfiguration($this->get('output', null));
    }
}
