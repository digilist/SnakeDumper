<?php

namespace Digilist\SnakeDumper\Converter\Service;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;

class SqlConverterService extends ConverterService
{

    /**
     * Read the configuration and create the converter service.
     *
     * @param DumperConfigurationInterface $config
     * @return static
     */
    public static function createFromConfig(DumperConfigurationInterface $config)
    {
        $converterService = new static();

        foreach ($config->getTableConfigs() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConverters() as $columnName => $converterDefinitions) {
                $key = sprintf('%s.%s', $tableName, $columnName);

                foreach ($converterDefinitions as $converterDefinition) {
                    $converter = $converterService->createConverterInstance($converterDefinition);
                    $converterService->addConverter($key, $converter, true);
                }
            }
        }

        return $converterService;
    }
}
