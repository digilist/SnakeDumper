<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\TableFilter;
use Doctrine\DBAL\Schema\Table;

class TableFilterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests whether a table is white listed works correctly.
     */
    public function testIsWhiteListed()
    {
        $config = new SqlDumperConfiguration();
        $config->setTableWhiteList(array('table1', 'table3'));

        $tableFilter = new TableFilter($config);
        $this->assertTrue($tableFilter->isTableWhiteListed(new Table('table1')));
        $this->assertFalse($tableFilter->isTableWhiteListed(new Table('table2')));
        $this->assertTrue($tableFilter->isTableWhiteListed(new Table('table3')));
    }

    /**
     * Tests whether the test whether a table is white listed works correctly.
     */
    public function testIsWhiteListed_Wildcard()
    {
        $config = new SqlDumperConfiguration();
        $config->setTableWhiteList(array('table*'));

        $tableFilter = new TableFilter($config);
        $this->assertTrue($tableFilter->isTableWhiteListed(new Table('table1')));
        $this->assertTrue($tableFilter->isTableWhiteListed(new Table('table2')));
        $this->assertTrue($tableFilter->isTableWhiteListed(new Table('table3')));
    }

    /**
     * Tests whether the selection of white listed tables works correctly.
     *
     * @test
     */
    public function testWhiteListFilter()
    {
        $config = new SqlDumperConfiguration();
        $tableFilter = new TableFilter($config);

        $table1 = new Table('table1');
        $table2 = new Table('table2');
        $allTables = array($table1, $table2);

        $filtered = $tableFilter->filterWhiteListTables($allTables);
        $this->assertEquals($allTables, $filtered);

        // Whitelist only one table
        $config->setTableWhiteList(array('table1'));
        $filtered = $tableFilter->filterWhiteListTables($allTables);
        $this->assertEquals(array($table1), $filtered);
    }

    /**
     * Tests whether a ignored table will be detected correctly.
     *
     * @test
     */
    public function testIgnoredTables()
    {
        $ignoredTableConfig = new TableConfiguration('ignored');
        $ignoredTableConfig->setIgnoreTable();

        $config = new SqlDumperConfiguration();
        $config->addTableConfig($ignoredTableConfig);
        $tableFilter = new TableFilter($config);

        $notIgnoredTable = new Table('not_ignored');
        $ignoredTable = new Table('ignored');

        $this->assertTrue($tableFilter->isTableNotIgnored($notIgnoredTable)); // not ignored, as not configured
        $this->assertFalse($tableFilter->isTableNotIgnored($ignoredTable)); // not ignored, as not configured

        $this->assertEquals(
            array($notIgnoredTable),
            $tableFilter->filterIgnoredTables(array($notIgnoredTable, $ignoredTable))
        );
    }
}
