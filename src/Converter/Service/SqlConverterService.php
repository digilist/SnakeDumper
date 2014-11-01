<?php

namespace Digilist\SnakeDumper\Converter\Service;

class SqlConverterService extends ConverterService
{

    /**
     * Read the configuration and create all necessary converter
     *
     */
    protected function initConverters()
    {
        foreach ($this->config->getTables() as $tableName => $table) {
            foreach ($table->getColumns() as $columnName => $column) {
                $key = sprintf('%s.%s', $tableName, $columnName);
                $this->addConvertersFromConfig($key, $column->getConverters());
            }
        }
    }
}
