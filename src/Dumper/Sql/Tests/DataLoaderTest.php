<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\DatabaseConfiguration;
use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Dumper\Sql\ConnectionHandler;
use Digilist\SnakeDumper\Dumper\Sql\DataLoader;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\SqlDumper;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class DataLoaderTest extends AbstractSqlTest
{

    /**
     * @var DataLoader
     */
    private $dataLoader;

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

        $dbConfig = new DatabaseConfiguration(['connection' => $this->connection]);
        $this->dataLoader = new DataLoader(new ConnectionHandler($dbConfig), new NullLogger());

        $refl = new \ReflectionObject($this->dataLoader);
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
     * Tests whether the isNotNull is built correctly.
     *
     * @test
     */
    public function testNotNullFilter()
    {
        $table = new Table('`Table`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Table',[
            'filters' => [
                ['isNotNull', 'column']
            ]
        ]);

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Table` t WHERE `column` IS NOT NULL';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether the isNull is built correctly.
     *
     * @test
     */
    public function testNullFilter()
    {
        $table = new Table('`Table`'); // Table name must be always quoted
        $tableConfig = new TableConfiguration('Table',[
            'filters' => [
                ['isNull', 'column']
            ]
        ]);

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Table` t WHERE `column` IS NULL';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether multiple filters will be AND-ed correctly
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

        $harvestedValues = array(
            'Customer' => array(
                'id' => array(10, 11, 12, 13),
            ),
        );
        $query = $this->createSelectQueryBuilder($tableConfig, $table, $harvestedValues)->getSQL();

        $expectedQuery = 'SELECT * FROM `Billing` t WHERE (`customer_id` IN (:param_0_0, :param_0_1, :param_0_2, :param_0_3)) OR (`customer_id` IS NULL)';
        $this->assertEquals($expectedQuery, $query);
    }


    /**
     * Tests whether the Composite filters are built correctly.
     *
     * @test
     */
    public function testCompositeFilters()
    {
        $table = new Table('`Table`');
        $tableConfig = new TableConfiguration('Table',[
            'filters' => [
                ['or', ['eq', 'col1', 1], ['eq', 'col2', 2] ,['eq', 'col3', 3]],
                ['and', ['gt', 'col1', 0], ['gt', 'col2', 2] ,['gt', 'col3', 3]]
            ]
        ]);

        $query = $this->createSelectQueryBuilder($tableConfig, $table)->getSQL();

        $expectedQuery = 'SELECT * FROM `Table` t WHERE ((`col1` = :param_0) OR (`col2` = :param_1) '
            .'OR (`col3` = :param_2)) AND ((`col1` > :param_3) AND (`col2` > :param_4) AND (`col3` > :param_5))';
        $this->assertEquals($expectedQuery, $query);
    }


    /**
     * Tests whether a table is white listed works correctly.
     */
    public function testRegularDependency()
    {
        $table1 = new Table('`Table1`');
        $table1Config = new TableConfiguration('Table1', [
            'dependencies' => [
                [
                    'column' => 'ref_id',
                    'referenced_table' => 'Table2',
                    'referenced_column' => 'id',
                    'condition' => ['eq', 'ref_table', 'Table2']
                ]
            ]
        ]);
        $harvestedValues = [
            'Table2' => [
                'id' => [1,2,3]
            ]
        ];

        $query = $this->createSelectQueryBuilder($table1Config, $table1, $harvestedValues)->getSQL();
        $expectedQuery = 'SELECT * FROM `Table1` t WHERE '
            .'((`ref_id` IN (:param_0_0, :param_0_1, :param_0_2)) OR (`ref_id` IS NULL)) AND (`ref_table` = :param_1)';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Tests whether a table is white listed works correctly.
     */
    public function testDoubleDependencyOnSameColumn()
    {
        $table1 = new Table('`Table1`');
        $table1Config = new TableConfiguration('Table1', [
            'dependencies' => [
                [
                    'column' => 'ref_id',
                    'referenced_table' => 'Table2',
                    'referenced_column' => 'id',
                    'condition' => ['eq', 'ref_table', 'Table2']
                ],
                [
                    'column' => 'ref_id',
                    'referenced_table' => 'Table3',
                    'referenced_column' => 'id',
                    'condition' => ['eq', 'ref_table', 'Table3']
                ]
            ]
        ]);
        $harvestedValues = [
            'Table2' => [
                'id' => [1,2,3]
            ],
            'Table3' => [
                'id' => [1]
            ]
        ];

        $query = $this->createSelectQueryBuilder($table1Config, $table1, $harvestedValues)->getSQL();
        $expectedQuery = 'SELECT * FROM `Table1` t WHERE '
            .'('
                .'((`ref_id` IN (:param_0_0, :param_0_1, :param_0_2)) OR (`ref_id` IS NULL)) AND (`ref_table` = :param_1)'
            .') '
            .'OR '
            .'('
                .'((`ref_id` IN (:param_2_0)) OR (`ref_id` IS NULL)) AND (`ref_table` = :param_3)'
            .')';
        $this->assertEquals($expectedQuery, $query);
    }


    /**
     * Tests whether a table is white listed works correctly.
     */
    public function testColumnAsReferencedTable()
    {
        $this->createTestDependenciesSchema();
        $config = new SqlDumperConfiguration([
            'database' => [
                'connection' => $this->connection,
            ],
            'output' => [
                'rows_per_statement' => 3,
            ],
            'tables' => [
                'Customer' => [
                    'limit' => 4,
                ],
                'BadgeMembership' => [
                    'dependencies' => [
                        [
                            'column' => 'item_id',
                            'column_as_referenced_table' => 'item_table',
                            'referenced_column' => 'id'
                        ]
                    ]
                ],
            ],
        ]);

        $context = new SqlDumperContext($config, new StringInput(''), new NullOutput());
        $dataLoader = new DataLoader($context->getConnectionHandler(), $context->getLogger());
        $context->getConfig()->hydrateConfig($dataLoader);

        /** @var SqlDumperConfiguration $config */
        $config = $context->getConfig();
        $badgeMembershipConfig = $config->getTableConfig('BadgeMembership');
        $badgeMembershipTable = new Table('`BadgeMembership`');

        $harvestedValues = [
            'Customer' => [
                'id' => [1,2,3,4]
            ],
            'SKU' => [
                'id' => [1,2]
            ]
        ];

        $query = $this->createSelectQueryBuilder($badgeMembershipConfig, $badgeMembershipTable, $harvestedValues);
        $sql = $query->getSQL();
        $parameters = $query->getParameters();

        $expectedSQL = 'SELECT * FROM `BadgeMembership` t WHERE '
            .'('
                .'((`item_id` IN (:param_0_0, :param_0_1, :param_0_2, :param_0_3)) OR (`item_id` IS NULL)) AND (`item_table` = :param_1)'
            .') '
            .'OR '
            .'('
                .'((`item_id` IN (:param_2_0, :param_2_1)) OR (`item_id` IS NULL)) AND (`item_table` = :param_3)'
            .')';
        $expectedParameters = [
            'param_0_0' => 1,
            'param_0_1' => 2,
            'param_0_2' => 3,
            'param_0_3' => 4,
            'param_1' => 'Customer',
            'param_2_0' => 1,
            'param_2_1' => 2,
            'param_3' => 'SKU',
        ];
        $this->assertEquals($expectedSQL, $sql);
        $this->assertEquals($expectedParameters, $parameters);
    }


    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $harvestedValues
     * @return QueryBuilder
     */
    private function createSelectQueryBuilder(TableConfiguration $tableConfig, Table $table, $harvestedValues = array())
    {
        return $this->createSelectQueryBuilder->invoke($this->dataLoader, $tableConfig, $table, $harvestedValues);
    }
}
