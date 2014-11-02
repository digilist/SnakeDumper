<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Converter\ConverterInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;

abstract class AbstractDumper implements DumperInterface
{

    /**
     * @var ConverterServiceInterface
     */
    private $converterService;

    /**
     * @param ConverterServiceInterface $converterService
     */
    public function setConverterService(ConverterServiceInterface $converterService)
    {
        $this->converterService = $converterService;
    }

    /**
     * @param string  $key
     * @param string  $value
     * @param array   $context
     *
     * @return mixed
     */
    protected function convert($key, $value, array $context = array())
    {
        return $this->converterService->convert($key, $value, $context);
    }
}
