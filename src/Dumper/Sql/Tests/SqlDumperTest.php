<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\DumperConfiguration;
use Digilist\SnakeDumper\Converter\Service\SqlConverterService;
use Digilist\SnakeDumper\Dumper\SqlDumper;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\Output;

class SqlDumperTest extends AbstractSqlTest
{

    public function testDumper()
    {
        $this->createTestSchema(true);

        $output = new TestOutput();
        $config = new DumperConfiguration(array(
            'output' => array(
                'rowsPerStatement' => 3,
            ),
            'tables' => array(
                'Billing' => array(
                    'filters' => array(
                        array(
                            'depends',
                            'customer_id',
                            'Customer.id',
                        )
                    ),
                ),
                'Customer' => array(
                    'limit' => 4,
                    'converters' => array(
                        'name' => array(
                            array('String' => 'Foobar'),
                        )
                    ),
                ),
                'RandomTable' => array(
                    'ignore_content' => true
                ),
                'RandomTable2' => array(
                    'ignore_table' => true
                ),
            ),
        ));

        $dumper = new SqlDumper($config, $output, new NullLogger(), $this->connection);
        $dumper->dump();

        $dump = $output->output;
        $this->assertEquals(file_get_contents(__DIR__ . '/test_dump.sql'), $dump);
    }
}

class TestOutput extends Output
{

    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
