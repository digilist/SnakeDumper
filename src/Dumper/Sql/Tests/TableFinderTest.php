<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\DumperConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
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
        $tableFinder = new TableFinder(new DumperConfiguration(), $this->connection);
        $this->assertEquals(array(), $tableFinder->findTables());
    }

    /**
     * Tests whether it runs correctly without any table.
     *
     * @test
     */
    public function testFindTables()
    {
        $tableFinder = new TableFinder(new DumperConfiguration(), $this->connection);

        $this->createTestSchema();

        $tables = $tableFinder->findTables();
        $this->assertEquals(2, count($tables));

        $billingTable = $tables[0];
        $customerTable = $tables[1];

        // Tables are returned in alphabetical order
        $this->assertEquals('Billing', $billingTable->getName());
        $this->assertEquals('Customer', $customerTable->getName());

        // Ensure names are quoted (because normally they wouldn't be, as the table names are no reserved keywords)
        // SQLite quotes with "
        $this->assertEquals('"Billing"', $billingTable->getQuotedName($this->platform));
        $this->assertEquals('"Customer"', $customerTable->getQuotedName($this->platform));

        // assure correct quotings on Billing table (representative for all other tables)
        $this->assertEquals(4, count($billingTable->getColumns()));
        $this->assertEquals('id', $billingTable->getColumn('id')->getName());
        $this->assertEquals('"id"', $billingTable->getColumn('id')->getQuotedName($this->platform));

        $this->assertEquals(2, count($billingTable->getIndexes()));
        $this->assertEquals('primary', $billingTable->getIndex('primary')->getName());
        $this->assertEquals('"primary"', $billingTable->getIndex('primary')->getQuotedName($this->platform));
        $this->assertEquals('billing_product', $billingTable->getIndex('billing_product')->getName());
        $this->assertEquals('"billing_product"', $billingTable->getIndex('billing_product')->getQuotedName($this->platform));

        // There is currently no ForeignKey Support for SQLite in Doctrine DBAL :(
        // see http://www.doctrine-project.org/jira/browse/DBAL-1065
        // and http://www.doctrine-project.org/jira/browse/DBAL-866
        // so we hope everything works correctly :-)
        /*
        $this->assertEquals(1, count($billingTable->getForeignKeys()));
        $this->assertEquals('customer_id', $billingTable->getForeignKey('customer_id')->getName());
        $this->assertEquals('"customer_id"', $billingTable->getForeignKey('customer_id')->getQuotedName($this->platform));
        */
    }

    /**
     * Tests whether a dependency between tables is correctly resolved.
     * We do not test complexer behaviours, as we expect that the dependency tree resolves it correctly.
     *
     * @test
     */
    public function testFindTablesWithDependencies()
    {
        $config = new DumperConfiguration();
        $config->addTable(new TableConfiguration('Customer'));
        $config->addTable(new TableConfiguration('Billing'));

        $config->getTable('Billing')->addDependency('Customer');

        $tableFinder = new TableFinder($config, $this->connection);
        $this->createTestSchema();

        $tables = $tableFinder->findTables();
        $this->assertEquals(2, count($tables));

        // Now Customer is selected first
        $this->assertEquals('Customer', $tables[0]->getName());
        $this->assertEquals('Billing', $tables[1]->getName());
    }
}
