<?php

namespace Digilist\SnakeDumper\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;

class PsrSQLLogger implements SQLLogger
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->logger->debug($sql);

        // TODO
        if ($params) {
            var_dump($params);
        }

        if ($types) {
            var_dump($types);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
