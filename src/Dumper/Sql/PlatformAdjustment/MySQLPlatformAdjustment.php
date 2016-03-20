<?php

namespace Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment;

use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use PDO;

class MySQLPlatformAdjustment implements PlatformAdjustmentInterface
{

    /**
     * @var ConnectionHandler
     */
    private $connectionHandler;

    /**
     * @param ConnectionHandler $connectionHandler
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
        $this->connectionHandler = $connectionHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function initConnection()
    {
        $this->connectionHandler->getPdo()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    /**
     * {@inheritdoc}
     */
    public function registerCustomTypeMappings()
    {
        $this->connectionHandler->getPlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getPreembleExtras()
    {
        return [
            'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
        ];
    }
}
