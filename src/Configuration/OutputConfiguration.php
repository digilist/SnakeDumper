<?php

namespace Digilist\SnakeDumper\Configuration;

class OutputConfiguration extends AbstractConfiguration
{

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->get('file');
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        return $this->set('file', $file);
    }

    /**
     * @return bool
     */
    public function getGzip()
    {
        return $this->get('gzip');
    }

    /**
     * @param bool $gzip
     * @return $this
     */
    public function setGzip($gzip)
    {
        $this->set('gzip', $gzip);
    }

    /**
     * @return bool
     */
    public function getRowsPerStatement()
    {
        return $this->get('rows_per_statement', 100);
    }

    /**
     * @param bool $rowsPerStatement
     * @return $this
     */
    public function setRowsPerStatement($rowsPerStatement)
    {
        $this->set('rows_per_statement', $rowsPerStatement);
    }
}
