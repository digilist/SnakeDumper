<?php

namespace Digilist\SnakeDumper\Converter\Helper;

class HouseNumberHelper
{

    /**
     * @var array
     */
    private static $letters = [
        '', 'a', 'b', 'c', 'd', 'e', 'f'
    ];

    /**
     * @return string
     */
    public static function generateNumber()
    {
        $number = mt_rand(1, 100);
        $letter = self::$letters[array_rand(self::$letters)];

        $houseNumber = $number . $letter;

        return $houseNumber;
    }

}
