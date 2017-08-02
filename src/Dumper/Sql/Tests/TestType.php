<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Class TestType created on 02.08.17.
 *
 * @author  moellers
 * @package Digilist\SnakeDumper\Dumper\Sql\Tests
 */
class TestType extends Type
{

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array                                     $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform         The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR(10)';
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     * @todo Needed?
     */
    public function getName()
    {
        return 'test';
    }

    /**
     * If this Doctrine Type maps to an already mapped database type,
     * reverse schema engineering can't take them apart. You need to mark
     * one of those types as commented, which will have Doctrine use an SQL
     * comment to typehint the actual Doctrine Type.
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
