<?php
namespace Digilist\SnakeDumper\Converter\Service;

interface ConverterServiceInterface
{

    /**
     * Convert the passed value with the converters, registered for the passed key.
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($key, $value, array $context = array());
}
