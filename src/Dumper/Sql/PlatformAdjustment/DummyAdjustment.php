<?php

namespace Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment;

use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use PDO;

class DummyAdjustment implements PlatformAdjustmentInterface
{

    /**
     * @param ConnectionHandler $connectionHandler
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerCustomTypeMappings()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function initConnection()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPreembleExtras()
    {
    }
}
