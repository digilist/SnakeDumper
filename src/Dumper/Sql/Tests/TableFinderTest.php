<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\DumperConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\IdentifierQuoter;
use Digilist\SnakeDumper\Dumper\Sql\TableFinder;

class TableFinderTest extends AbstractSqlTest
{

    /**
     * Tests whether it runs correctly without any table.
     *
     * @test
     */
    public function testWithoutTables()
    {
        $tableFinder = new TableFinder($this->connection);
        $this->assertEquals(array(), $tableFinder->findTables(new DumperConfiguration()));
    }

    /**
     * Tests whether the table finder works correctly and if the identifier quoting works.
     *
     * @test
     */
    public function testFindTables()
    {
        $tableFinder = new TableFinder($this->connection);
        $identifierQuoter = new IdentifierQuoter($this->connection);

        $this->createTestSchema();

        $tables = $tableFinder->findTables(new DumperConfiguration());
        $tables = $identifierQuoter->quoteTables($tables);

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
}
