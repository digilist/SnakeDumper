<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\Dumper\StructureDumper;
use Digilist\SnakeDumper\Dumper\Sql\IdentifierQuoter;
use Digilist\SnakeDumper\Dumper\Sql\TableSelector;

class TableSelectorTest extends AbstractSqlTest
{

    /**
     * Tests whether it runs correctly without any table.
     *
     * @test
     */
    public function testWithoutTables()
    {
        $tableSelector = new TableSelector($this->connection);
        $this->assertEquals(array(), $tableSelector->findTablesToDump(new SqlDumperConfiguration()));
    }

    /**
     * Tests whether the table selecter works correctly and if the identifier quoting works.
     *
     * @test
     */
    public function testSelectTables()
    {
        $tableSelector = new TableSelector($this->connection);
        $this->createTestSchema();

        $tables = $tableSelector->findTablesToDump(new SqlDumperConfiguration());

        $this->assertEquals(2, count($tables));

        // Billing depends on Customer, so Customer is selected first
        $customerTable = $tables[0];
        $billingTable = $tables[1];

        // Tables are returned in alphabetical order
        $this->assertEquals('Billing', $billingTable->getName());
        $this->assertEquals('Customer', $customerTable->getName());

        // Ensure names are quoted (because normally they wouldn't be, as the table names are no reserved keywords)
        // MySQL quotes with `
        $this->assertEquals('`Billing`', $billingTable->getQuotedName($this->platform));
        $this->assertEquals('`Customer`', $customerTable->getQuotedName($this->platform));

        // assure correct quotings on Billing table (representative for all other tables)
        $this->assertEquals(4, count($billingTable->getColumns()));
        $this->assertEquals('id', $billingTable->getColumn('id')->getName());
        $this->assertEquals('`id`', $billingTable->getColumn('id')->getQuotedName($this->platform));

        $this->assertEquals(3, count($billingTable->getIndexes()));
        $this->assertEquals('PRIMARY', $billingTable->getIndex('PRIMARY')->getName());
        $this->assertEquals('`PRIMARY`', $billingTable->getIndex('PRIMARY')->getQuotedName($this->platform));
        $this->assertEquals('billing_product', $billingTable->getIndex('billing_product')->getName());
        $this->assertEquals('`billing_product`', $billingTable->getIndex('billing_product')->getQuotedName($this->platform));

        $this->assertEquals(1, count($billingTable->getForeignKeys()));
        $this->assertEquals('customer_id', $billingTable->getForeignKey('customer_id')->getName());
        $this->assertEquals('`customer_id`', $billingTable->getForeignKey('customer_id')->getQuotedName($this->platform));
    }

    /**
     * Tests that the dumper does not get confused by column comments that indicate custom doctrine types (which are
     * not registered).
     *
     * @test
     */
    public function testDoctrineCustomTypes()
    {
        $pdo = $this->connection->getWrappedConnection();
        $pdo->query('CREATE TABLE custom_doctrine_type (
                         foobar VARCHAR(255) NOT NULL COMMENT \'(DC2Type:example)\'
                     )');

        $tableSelector = new TableSelector($this->connection);
        $tables = $tableSelector->findTablesToDump(new SqlDumperConfiguration());

        $this->assertEquals('string', $tables[0]->getColumn('foobar')->getType()->getName());
        $this->assertEquals('(DC2Type:example)', $tables[0]->getColumn('foobar')->getComment());
    }
}
