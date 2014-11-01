<?php

namespace Digilist\SnakeDumper\Dumper;

use Digilist\SnakeDumper\Converter\ConverterInterface;

abstract class AbstractDumper implements DumperInterface
{

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @param string  $table
     * @param string  $column
     * @param string  $value
     * @param array   $context
     *
     * @return mixed
     */
    protected  function convert($table, $column, $value, array $context = [])
    {
        $this->converter->convert($table, $column, $value, $context);
    }

    /**
     * @param ConverterInterface $converter
     */
     public function setConverter(ConverterInterface $converter)
     {
         $this->converter = $converter;
     }
 }
