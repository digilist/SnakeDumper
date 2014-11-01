<?php

namespace Digilist\SnakeDumper\Configuration;

interface DatabaseConfigurationInterface
{
    /**
     * @return string
     */
    public function getHost();

    /**
     * @return string
     */
    public function getDriver();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getUser();

    /**
     * @return string
     */
    public function getDatabaseName();
}
