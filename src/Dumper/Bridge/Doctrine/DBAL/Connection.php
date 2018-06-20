<?php

namespace Digilist\SnakeDumper\Dumper\Bridge\Doctrine\DBAL;

/**
 * Extends the Doctrine DBAL Connection wrapper to support our own schema manager.
 *
 * This allows us to use SnakeDumper on databases that use custom Doctrine types which are not known to SnakeDumper's
 * Doctrine instance.
 */
class Connection extends \Doctrine\DBAL\Connection
{
    /**
     * Replace the native schema manager with our own implementation which removes the custom type handling of Doctrine.
     *
     * We do not need this and it prevents the dumping when the type is not known.
     *
     * This is actually really hacky, but I do not see another way to solve this problem at the moment without creating
     * own implementations (inheriting from the original one) for every database platform / schema manager.
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager|void
     */
    public function getSchemaManager()
    {
        if ( ! $this->_schemaManager) {
            $this->_schemaManager = $this->createSchemaManager();
        }

        return $this->_schemaManager;
    }

    private function createSchemaManager()
    {
        $originalSchemaManager = $this->_driver->getSchemaManager($this);
        $originalSchemaManagerClass = get_class($originalSchemaManager);

        $schemaManagerName = 'SnakeDumperSchemaManager' . uniqid();
        $schemaManagerCode = <<<EOT
    class $schemaManagerName extends \\$originalSchemaManagerClass {
        public function extractDoctrineTypeFromComment(\$comment, \$currentType)
        {
            // Do not extract the doctrine type.
            return \$currentType;
        }
    
        public function removeDoctrineTypeFromComment(\$comment, \$type)
        {
            // Do not remove the Doctrine type from the comment
            return \$comment;
        }
    }
EOT;
//        echo($schemaManagerCode);exit;
        eval($schemaManagerCode);

        return new $schemaManagerName($this);
    }
}
