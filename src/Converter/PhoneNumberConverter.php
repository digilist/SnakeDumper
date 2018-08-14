<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;

/**
 * The PhoneConverter replaces a value
 * with a random phoneNumber.
 */
class PhoneNumberConverter extends FakerConverter
{

    const DEFAULT_LENGTH = 10;
    const DEFAULT_PREFIX = '';

    /**
     * @var string
     */
    private $prefix = self::DEFAULT_PREFIX;

    /**
     * @var int
     */
    private $length = self::DEFAULT_LENGTH;


    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        if (isset($parameters['prefix'])) {
            $this->prefix = $parameters['prefix'];
        }
        if  (isset($parameters['length'])) {
            $this->length = $parameters['length'];
        }
    }

    /**
     * Convert phone number
     *
     * @param string $value
     * @param array $context
     * @return mixed|string
     */
    public function convert($value, array $context = array())
    {
        $fakeNumber = $this->prefix;
        for ($i = $this->length; $i > 0; --$i) {
            $fakeNumber .= (string) mt_rand(0, 9);
        }

        return $fakeNumber;
    }
}
