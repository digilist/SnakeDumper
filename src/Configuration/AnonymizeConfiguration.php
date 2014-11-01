<?php /** File containing class AnonymizeConfiguration */

namespace Digilist\SnakeDumper\Configuration;

class AnonymizeConfiguration extends AbstractConfiguration implements AnonymizeConfigurationInterface
{

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->get('format');
    }

    protected function parseConfig(array $oldConfig, array $newConfig)
    {
        $this->ensureHas('format');
    }
}
