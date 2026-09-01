<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Shared\Lib\String;

/**
 * General-purpose string utilities.
 */
final class StringUtils
{
    /**
     * Strips leading/trailing whitespace, removes diacritics, and optionally changes the string's case.
     * Diacritics are replaced with their ASCII equivalents (e.g. `é` → `e`, `œ` → `oe`)
     * before case conversion is applied, making the output safe for case-insensitive comparisons.
     * @param string $s The input string to sanitize.
     * @param StringCase $c The case transformation to apply (default: {@see StringCase::NONE}).
     * @return string The sanitized string.
     */
    public static function sanitize(string $s, StringCase $c = StringCase::NONE): string
    {
        $temp = trim(str_replace(
            ['à','â','ä','á','ã','å','À','Â','Ä','Á','Ã','Å',
                'è','ê','ë','é','È','Ê','Ë','É',
                'ì','î','ï','í','Ì','Î','Ï','Í',
                'ò','ô','ö','ó','õ','ø','Ò','Ô','Ö','Ó','Õ','Ø',
                'ù','û','ü','ú','Ù','Û','Ü','Ú',
                'y','ÿ','Ý',
                'ñ','Ñ',
                'ç','Ç',
                'æ','Æ','œ','Œ'],
            ['a','a','a','a','a','a','A','A','A','A','A','A',
                'e','e','e','e','E','E','E','E',
                'i','i','i','i','I','I','I','I',
                'o','o','o','o','o','o','O','O','O','O','O','O',
                'u','u','u','u','U','U','U','U',
                'y','y','Y',
                'n','N',
                'c','C',
                'ae','AE','oe','OE'],
            $s,
        ));
        return match ($c) {
            StringCase::NONE => $temp,
            StringCase::LOWER => strtolower($temp),
            StringCase::UPPER => strtoupper($temp),
        };
    }

    /**
     * Separates a full name with the LAST First/LAST first format and returns the first and last name.
     * @param string $full The full name to split.
     * @param bool $getRaw A special flag to indicate that the names part should be returned
     * as lists instead of a string.
     * @return array The two names inside an array containing the following: [first, last].
     */
    public static function separateNames(string $full, bool $getRaw = false): array
    {
        $parts = explode(" ", $full)
                    |> (fn($x) => array_map(fn(string $s) => trim($s), $x))
                    |> (fn($x) => array_filter($x, fn(string $s) => $s !== ''))
                    |> (fn($x) => array_map(fn(string $s) => preg_replace("/-+/", "-", $s), $x));

        $lastNameParts = array_values(array_filter($parts, fn($n) => strtoupper($n) === $n));
        $firstNameParts = array_values(array_filter($parts, fn($n) => strtoupper($n) !== $n));

        return $getRaw
                ? [$firstNameParts, $lastNameParts]
                : [implode(" ", $firstNameParts), implode(" ", $lastNameParts)];
    }

    /**
     * Obfuscates a string by only showing the first characters and then a random number of asterisks (*).
     * @param string $_s The string to obfuscate.
     * @param int $n The number of characters to show before the asterisks.
     * @param int $min The minimum number of asterisks (default = 3).
     * @param int $max The maximum number of asterisks (default = 7).
     * @return string The obfuscated string.
     */
    public static function obfuscate(string $_s, int $n, int $min = 3, int $max = 7): string
    {
        return substr($_s, 0, $n) . str_repeat('*', max($min, strlen($_s) - rand($min, $max) - $n));
    }
}
