<?php

namespace Digilist\SnakeDumper\Converter\Service;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;

class SqlDataConverter extends DataConverter
{

    /**
     * Create a new SqlDataConvert by reading the configuration and adding converters for the sql dumper.
     *
     * @param DumperConfigurationInterface $config
     */
    public function __construct(DumperConfigurationInterface $config)
    {
        foreach ($config->getTableConfigs() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConverters() as $columnName => $converterDefinitions) {
                $key = sprintf('%s.%s', $tableName, $columnName);

                foreach ($converterDefinitions as $converterDefinition) {
                    $converter = $this->createConverterInstance($converterDefinition);
                    $this->addConverter($key, $converter, true);
                }
            }
        }
    }
}
