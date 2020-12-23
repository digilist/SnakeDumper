<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Converter\Service\DataConverterInterface;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\Context\DumperContextInterface;
use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DumperInterface
{

    /**
     * DumperInterface constructor.
     * @param DumperContextInterface $context
     */
    public function __construct(DumperContextInterface $context);

    /**
     * This function starts the dump process.
     *
     * The passed context contains all information
     *
     * @param DumperContextInterface $context
     *
     * @return void
     */
    public function dump();

}
