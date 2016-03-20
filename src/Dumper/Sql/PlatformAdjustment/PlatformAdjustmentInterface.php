<?php

namespace Digilist\SnakeDumper\Dumper\Sql\PlatformAdjustment;

use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;

interface PlatformAdjustmentInterface
{

    /**
     * @param ConnectionHandler $connectionHandler
     */
    public function __construct(ConnectionHandler $connectionHandler);

    /**
     * Initializes the connection.
     *
     * For example, this function should disable the result buffer.
     *
     * @return void
     */
    public function initConnection();

    /**
     * Register custom type mappings that Doctrine should handle.
     *
     * @return void
     */
    public function registerCustomTypeMappings();

    /**
     * This function returns additional lines of SQL code that should be included into the dump.
     *
     * Those statements will be executed before any other statement in the dump. Each item in the array will be printed
     * as one row in the dump.
     *
     * @return string[]
     */
    public function getPreembleExtras();
}
