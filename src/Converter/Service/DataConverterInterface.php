<?php
namespace Digilist\SnakeDumper\Converter\Service;

interface DataConverterInterface
{

    /**
     * Convert the passed value with the converters, registered for the passed key.
     *
     * The passed key can be, for example, "table.column_name" for sql databases. The value is the current
     * value in the database and the context could (should?) contain the values of other columns, so that those
     * values can be used by a converter.
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $context
     *
     * @return string
     */
    public function convert($key, $value, array $context = array());
}
