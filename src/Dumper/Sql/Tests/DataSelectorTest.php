<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\DataSelector;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;

class DataSelectorTest extends AbstractSqlTest
{

    /**
     * @var DataSelector
     */
    private $dataSelector;

    /**
     * @var \ReflectionMethod
     */
    private $createSelectQueryBuilder;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->dataSelector = new DataSelector($this->connection);

        $refl = new \ReflectionObject($this->dataSelector);
        $createSelectQueryBuilder = $refl->getMethod('createSelectQueryBuilder');
        $createSelectQueryBuilder->setAccessible(true);

        $this->createSelectQueryBuilder = $createSelectQueryBuilder;
    }

    /**
     * Tests whether the standard select query is build correctly.
     *
     * @test
     */
    public function testStandardQuery()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted

        $query = $this->createSelectQueryBuilder(new TableConfiguration('Customer'), $table)->getSQL();
        $this->assertEquals('SELECT * FROM `Customer` t', $query);
    }

    /**
     * Tests whether a select query with limit is build correctly.
     *
     * @test
     */
    public function testLimit()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Customer');
        $tableConfig->setLimit(100);

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();
        $this->assertEquals('SELECT * FROM `Customer` t LIMIT 100', $query);
    }

    /**
     * Tests whether a select query with order by is build correctly.
     *
     * @test
     */
    public function testOrderBy()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Customer');
        $tableConfig->setOrderBy('id DESC');

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();
        $this->assertEquals('SELECT * FROM `Customer` t ORDER BY id DESC', $query);
    }

    /**
     * Tests whether a filter is used correctly.
     * We only test a single filter, as we expect Doctrine Expressions to work correctly.
     *
     * @test
     */
    public function testBasicFilter()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Customer', array(
            'filters' => array(
                array(
                    'eq',
                    'id',
                    1
                ),
            ),
        ));

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Customer` t WHERE `id` = :param_0';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether the IN filter is build correctly.
     *
     * @test
     */
    public function testInFilter()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Customer', array(
            'filters' => array(
                array(
                    'in',
                    'id',
                    array(1, 2, 3),
                ),
            ),
        ));

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Customer` t WHERE `id` IN (:param_0_0, :param_0_1, :param_0_2)';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether multiple filters will be AND-ed correclty
     *
     * @test
     */
    public function testMultipleFilter()
    {
        $table = new Table('`Customer`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Customer', array(
            'filters' => array(
                array(
                    'lt',
                    'id',
                    100,
                ),
                array(
                    'eq',
                    'name',
                    'Markus',
                ),
            ),
        ));

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Customer` t WHERE (`id` < :param_0) AND (`name` = :param_1)';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether multiple filters will be AND-ed correclty
     *
     * @test
     */
    public function testDataDependentFilter()
    {
        $table = new Table('`Billing`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Billing', array(
            'filters' => array(
                array(
                    'depends',
                    'customer_id',
                    'Customer.id',
                ),
            ),
        ));

        $collectedValues = array(
            'Customer' => array(
                'id' => array(10, 11, 12, 13),
            ),
        );
        $query = $this->createSelectQueryBuilder($tableConfig, $table, $collectedValues)->getSQL();

        $expectedQuery = 'SELECT * FROM `Billing` t WHERE (`customer_id` IN (:param_0_0, :param_0_1, :param_0_2, :param_0_3)) OR (`customer_id` IS NULL)';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $collectedValues
     * @return QueryBuilder
     */
    private function createSelectQueryBuilder(TableConfiguration $tableConfig, Table $table, $collectedValues = array())
    {
        return $this->createSelectQueryBuilder->invoke($this->dataSelector, $tableConfig, $table, $collectedValues);
    }
}
