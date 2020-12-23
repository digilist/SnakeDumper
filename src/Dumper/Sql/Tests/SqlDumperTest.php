<?php

namespace Digilist\SnakeDumper\Dumper\Sql\Tests;

use Digilist\SnakeDumper\Configuration\SqlDumperConfiguration;
use Digilist\SnakeDumper\Converter\Service\SqlDataConverter;
use Digilist\SnakeDumper\Dumper\Sql\SqlDumperContext;
use Digilist\SnakeDumper\Dumper\SqlDumper;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;

class SqlDumperTest extends AbstractSqlTest
{

    public function testDumper()
    {
        $this->createTestSchema(true);

        $output = new TestOutput();
        $config = new SqlDumperConfiguration(array(
            'database' => [
                'connection' => $this->connection,
            ],
            'output' => array(
                'rows_per_statement' => 3,
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

        $context = new SqlDumperContext($config, new StringInput(''), new NullOutput());
        $context->setDumpOutput($output);

        $dumper = new SqlDumper($context);
        $dumper->dump();

        $dump = $output->output;

        $this->markTestSkipped(
            'This test does not produce the same output every time'
        );

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
