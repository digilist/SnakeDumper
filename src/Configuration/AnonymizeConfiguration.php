<?php /** File containing class AnonymizeConfiguration */

namespace Digilist\SnakeDumper\Configuration;

class AnonymizeConfiguration extends AbstractConfiguration implements AnonymizeConfigurationInterface
{

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->get('format', null);
    }

    protected function parseConfig(array $config)
    {
        $this->ensureHas('format');
    }
}
