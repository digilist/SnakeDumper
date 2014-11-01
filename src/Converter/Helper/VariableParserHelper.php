<?php

namespace Digilist\SnakeDumper\Converter\Helper;

use Digilist\SnakeDumper\Exception\VariableParserException;

/**
 * The VariableParserHelper helps parsing variable expression strings.
 */
class VariableParserHelper
{

    const LEFT_DEFAULT = '{{';
    const RIGHT_DEFAULT = '}}';

    /**
     * @param string $str
     *      Subjected string containing variable expressions.
     * @param array $replacements
     *      Dictionary with replacement values, like {'search' => 'replacement'}.
     * @param string $left
     *      The left delimiter for a variable expression. Default is `{{`.
     * @param string $right
     *      The right delimiter for a variable expression. Default is `}}`.
     * @return string
     * @throws \Digilist\SnakeDumper\Exception\VariableParserException
     */
    public static function parse($str, array $replacements, $left = self::LEFT_DEFAULT, $right = self::RIGHT_DEFAULT)
    {
        // Left delimiter not found? Return
        if (strpos($str, $left) === false) {
            return $str;
        }

        // Escape for regex
        $left = preg_quote($left);
        $right = preg_quote($right);

        $regex = '/' . $left . '\\s*(\\w+)\\s*' . $right . '/';

        /**
         * Handler for a variable expression.
         *
         * @param array $matches
         * @return string
         * @throws \Digilist\SnakeDumper\Exception\VariableParserException
         */
        $varExpressionHandler = function (array $matches) use ($replacements) {
            $key = $matches[1];
            if (isset($replacements[$key])) {
                return $replacements[$key];
            }

            throw new VariableParserException('No value defined for `' . $key . '`.');
        };

        return preg_replace_callback($regex, $varExpressionHandler, $str);
    }
}
