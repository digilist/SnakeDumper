<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\DataConverterInterface;
use Digilist\SnakeDumper\Converter\Service\SqlDataConverter;
use Digilist\SnakeDumper\Dumper\Context\AbstractDumperContext;
use Digilist\SnakeDumper\Dumper\Helper\ProgressBarHelper;
use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SqlDumperContext extends AbstractDumperContext
{

    /**
     * @var SqlDataConverter
     */
    protected $dataConverter;

    /**
     * @var ConnectionHandler
     */
    protected $connectionHandler;

    /**
     * @param DumperConfigurationInterface $config
     * @param InputInterface               $applicationInput
     * @param OutputInterface              $applicationOutput
     */
    public function __construct(
        DumperConfigurationInterface $config,
        InputInterface $applicationInput,
        OutputInterface $applicationOutput
    ) {
        parent::__construct($config, $applicationInput, $applicationOutput);

        $this->dataConverter = new SqlDataConverter($config);
        $this->connectionHandler = new ConnectionHandler($config->getDatabaseConfig());
    }

    /**
     * @return ConnectionHandler
     */
    public function getConnectionHandler()
    {
        return $this->connectionHandler;
    }

    /**
     * @return ProgressBarHelper
     */
    public function getProgressBarHelper()
    {
        return $this->progressBarHelper;
    }

    /**
     * @return DataConverterInterface
     */
    public function getDataConverter()
    {
        return $this->dataConverter;
    }
}
