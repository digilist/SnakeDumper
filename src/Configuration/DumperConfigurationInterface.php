<?php /** File containing interface DumperConfigurationInterface */

namespace Digilist\SnakeDumper\Configuration;

/**
 * @package Configuration
 * @author moellers
 */
interface DumperConfigurationInterface
{

    /**
     * @return DatabaseConfigurationInterface
     */
    public function getDatabase();

    /**
     * @return CommonConfigurationInterface
     */
    public function getSettings();

    /**
     * @return AnonymizeConfigurationInterface
     */
    public function getAnonymize();
}
