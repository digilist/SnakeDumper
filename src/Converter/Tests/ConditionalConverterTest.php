<?php

namespace Digilist\SnakeDumper\Converter\Tests;

use Digilist\SnakeDumper\Converter\ConditionalConverter;
use Digilist\SnakeDumper\Converter\Service\ConverterService;

class ConditionalConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testConditionalIfTrue()
    {
        $ifTrue = 'Hello World';

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'if_true' => $ifTrue,
        ]);

        $this->assertEquals($ifTrue, $conditionalConverter->convert('ABC', ['title' => '']));
        $this->assertEquals('ABC', $conditionalConverter->convert('ABC', ['title' => 'Not Empty']));
    }

    public function testConditionalIfFalse()
    {
        $ifFalse = 'This is false';

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'if_false' => $ifFalse,
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
            'if_true' => $ifTrue,
            'if_false' => $ifFalse,
        ]);

        $this->assertEquals($ifTrue, $conditionalConverter->convert('ABC', ['title' => '']));
        $this->assertEquals($ifFalse, $conditionalConverter->convert('ABC', ['title' => 'Not Empty']));
    }

    public function testConditionalConverter()
    {
        $ifTrueConverters = [['Replace' => ['search' => 'foo', 'replace' => 'bar']]];
        $ifFalseConverters = [['Replace' => ['search' => 'bar', 'replace' => 'foo']]];

        $conditionalConverter = new ConditionalConverter([
            'condition' => '$title == ""',
            'if_true_converters' => $ifTrueConverters,
            'if_false_converters' => $ifFalseConverters,
        ]);

        $this->assertEquals('barbar', $conditionalConverter->convert('foobar', ['title' => '']));
        $this->assertEquals('foofoo', $conditionalConverter->convert('foobar', ['title' => 'Not Empty']));
    }
}
