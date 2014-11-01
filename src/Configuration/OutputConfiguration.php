<?php

namespace Digilist\SnakeDumper\Configuration;

class OutputConfiguration extends AbstractConfiguration
{

    private $file;
    private $gzip;

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     */
    public function getGzip()
    {
        return $this->gzip;
    }

    /**
     * @param bool $gzip
     * @return $this
     */
    public function setGzip($gzip)
    {
        $this->gzip = $gzip;
        return $this;
    }

    protected function parseConfig(array $config)
    {
    }
}
