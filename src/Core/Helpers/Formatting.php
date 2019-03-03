<?php

namespace MageArab\MegaFramework\Helpers;

class Formatting
{
    /**
     * Sanitize Html Class using Array
     *
     * @param array $class The list of the classes to sanitize
     *
     * @return string The sanitized class values
     */
    function sanitizeHtmlClassByArray(array $class): string
    {
        $class = array_reduce($class, function (string $carry, string $item): string {
            return rtrim($carry, ' ') . ' ' . sanitize_html_class($item);
        }, '');
        return trim($class, ' ');
    }

    /**
     * Convert String To Boolean
     *
     * This function is the same of wc_string_to_bool.
     *
     * @param string $value The string to convert to boolean. 'yes', 'true', '1' are converted to true.
     *
     * @return bool True or false depending on the passed value.
     */
    function stringToBool(string $value): bool
    {
        return (
            'yes' === $value
            || 'true' === $value
            || '1' === $value
            || 'on' === $value
        );
    }

    /**
     * Convert Boolean to String
     *
     * This function is the same of wc_bool_to_string
     *
     * @param bool $bool The bool value to convert.
     *
     * @return string The converted value. 'yes' or 'no'.
     */
    function boolToString(bool $bool): string
    {
        return true === $bool ? 'yes' : 'no';
    }

    public static function makeSingular($word)
    {
        $rules = array(
            'ss' => false,
            'os' => 'o',
            'ies' => 'y',
            'xes' => 'x',
            'oes' => 'o',
            'ves' => 'f',
            'es' => '',
            's' => '',
        );
        foreach (array_keys($rules) as $key) {
            if (substr($word, (strlen($key) * -1)) != $key) {
                continue;
            }
            if ($key === false) {
                return $word;
            }
            return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key];
        }
        return $word;
    }

    public static function cleanString($string)
    {
        if (!is_string($string)) {
            return $string;
        }
        return trim(strip_shortcodes(strip_tags($string)));
    }
}