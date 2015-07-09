<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\ConverterServiceInterface;
use Digilist\SnakeDumper\Converter\Service\SqlConverterService;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractDumper implements DumperInterface
{

    /**
     * @var DumperConfigurationInterface
     */
    protected $config;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ConverterServiceInterface
     */
    private $converterService;

    /**
     * @param DumperConfigurationInterface $config
     * @param OutputInterface              $output
     */
    public function __construct(DumperConfigurationInterface $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->output = $output;
    }

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
