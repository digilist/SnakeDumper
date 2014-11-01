<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Converter\ConverterInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;

abstract class AbstractDumper implements DumperInterface
{

    /**
     * @var ConverterServiceInterface
     */
    private $converter;

    /**
     * @param ConverterServiceInterface $converter
     */
    public function setConverter(ConverterServiceInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param string  $key
     * @param string  $value
     * @param array   $context
     *
     * @return mixed
     */
    protected function convert($key, $value, array $context = [])
    {
        return $this->converter->convert($key, $value, $context);
    }
}
