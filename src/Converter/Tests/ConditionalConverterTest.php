<?php

namespace Digilist\SnakeDumper\Converter\Tests;

use Digilist\SnakeDumper\Converter\ConditionalConverter;

class ConditionalConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testConditionalIfTrue()
    {
        $ifTrue = 'Hello World';

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'ifTrue' => $ifTrue,
        ]);

        $this->assertEquals($ifTrue, $conditionalConverter->convert('ABC', ['title' => '']));
        $this->assertEquals('ABC', $conditionalConverter->convert('ABC', ['title' => 'Not Empty']));
    }

    public function testConditionalIfFalse()
    {
        $ifFalse = 'This is false';

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'ifFalse' => $ifFalse,
        ]);

        $this->assertEquals($ifFalse, $conditionalConverter->convert('ABC', ['title' => 'Not Empty']));
        $this->assertEquals('ABC', $conditionalConverter->convert('ABC', ['title' => '']));
    }

    public function testConditional()
    {
        $ifTrue = 'This is true';
        $ifFalse = 'This is false';

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'ifTrue' => $ifTrue,
            'ifFalse' => $ifFalse,
        ]);

        $this->assertEquals($ifTrue, $conditionalConverter->convert('ABC', ['title' => '']));
        $this->assertEquals($ifFalse, $conditionalConverter->convert('ABC', ['title' => 'Not Empty']));
    }
}
