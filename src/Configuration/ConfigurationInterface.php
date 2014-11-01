<?php /** File containing interface ConfigurationInterface */

namespace Digilist\SnakeDumper\Configuration;

/**
 * @package Configuration
 * @author moellers
 */
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

    /**
     * @return string
     */
    public function getNodeName();
}
