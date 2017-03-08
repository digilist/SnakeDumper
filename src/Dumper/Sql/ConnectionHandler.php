<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\DatabaseConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment\DummyAdjustment;
use Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment\MySQLPlatformAdjustment;
use Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment\PlatformAdjustmentInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PDO;

class ConnectionHandler
{

    /**
     * @var DatabaseConfiguration
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PlatformAdjustmentInterface|null
     */
    private $platformAdjustment;

    /**
     * ConnectionHandler constructor.
     *
     * @param DatabaseConfiguration $config
     */
    public function __construct(DatabaseConfiguration $config)
    {
        $this->config = $config;

        if ($config->getConnection() !== null) {
            $this->connection = $config->getConnection();
            $this->initPlatformAdjustment($this->connection);
        }
    }

    /**
     * Connect to the database.
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return Connection
     */
    public function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        return $this->connect();
    }

    /**
     * This method checks whether the connection is still open or reconnects otherwise.
     *
     * The connection might drop in some scenarios, where the server has a configured timeout and the handling
     * of the result set takes longer. To prevent failures of the dumper, the connection will be opened again.
     */
    public function reconnectIfNecessary()
    {
        if (!$this->connection->ping()) {
            $this->connection->close();
            $this->connect();
        }
    }

    /**
     * Returns the database platform.
     *
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    public function getPlatform()
    {
        return $this->getConnection()->getDatabasePlatform();
    }

    /**
     * Returns the PDO instance that is the basis for the connection.
     *
     * @return PDO
     */
    public function getPdo()
    {
        return $this->getConnection()->getWrappedConnection();
    }

    /**
     * @return PlatformAdjustmentInterface|null
     */
    public function getPlatformAdjustment()
    {
        return $this->platformAdjustment;
    }

    /**
     * Open the database connection.
     *
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function connect()
    {
        $connectionParams = array(
            'driver'   => $this->config->getDriver(),
            'host'     => $this->config->getHost(),
            'port'     => $this->config->getPort(),
            'user'     => $this->config->getUser(),
            'password' => $this->config->getPassword(),
            'dbname'   => $this->config->getDatabaseName(),
            'charset'  => $this->config->getCharset(),
        );

        $dbalConfig = new Configuration();
        $this->connection = DriverManager::getConnection($connectionParams, $dbalConfig);
        $this->connection->connect();

        $this->initPlatformAdjustment($this->connection);
        $this->platformAdjustment->initConnection();
        $this->platformAdjustment->registerCustomTypeMappings();

        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    private function initPlatformAdjustment(Connection $connection)
    {
        $adjustments = [
            MySqlPlatform::class => MySQLPlatformAdjustment::class
        ];

        foreach ($adjustments as $platformClass => $adjustmentClass) {
            if ($connection->getDatabasePlatform() instanceof $platformClass) {
                $this->platformAdjustment = new $adjustmentClass($this);

                return;
            }
        }

        $this->platformAdjustment = new DummyAdjustment($this);
    }
}
