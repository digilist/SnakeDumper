<?php

namespace Digilist\SnakeDumper\Converter\Tests;

use Digilist\SnakeDumper\Converter\RandomConverter;
use Digilist\SnakeDumper\Exception\InvalidArgumentException;

/**
 * @package Digilist\SnakeDumper\Converter\Tests
 * @author moellers
 */
class RandomConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testEqualBounds()
    {
        foreach (array(0, 1, 2, 42, 1337, -1, -42) as $i) {
            $c1 = new RandomConverter(array('min' => $i, 'max' => $i));
            $this->assertEquals($i, $c1->convert('foo'));
        }
    }

    public function testMin()
    {
        $c1 = new RandomConverter(array('min' => 50, 'max' => 100));
        $this->assertEquals(50, $c1->getMin());

        $c1 = new RandomConverter();
        $this->assertEquals(0, $c1->getMin());

        $c1 = new RandomConverter(array('min' => 100, 'max' => 50));
        $this->assertEquals(50, $c1->getMin());
    }

    public function testMax()
    {
        $c1 = new RandomConverter(array('min' => 50, 'max' => 100));
        $this->assertEquals(100, $c1->getMax());

        $c1 = new RandomConverter();
        $this->assertEquals(mt_getrandmax(), $c1->getMax());

        $c1 = new RandomConverter(array('min' => 100, 'max' => 50));
        $this->assertEquals(100, $c1->getMax());
    }

    public function testStep()
    {
        $c1 = new RandomConverter(array('min' => 0, 'max' => 1, 'step' => 0.25));
        $this->assertEquals(0.25, $c1->getStep());

        // Correctness
        $this->assertContains($c1->convert('foo'), array(0, 0.25, 0.5, 0.75, 1));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidStepValue()
    {
        new RandomConverter(array('step' => 0));
    }
}
