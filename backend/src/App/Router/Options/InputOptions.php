<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Router\Options;

use Goralys\Shared\Error\GoralysValueError;

/**
 * Builder for route input-validation option arrays.
 * The produced arrays are merged into a route's options and processed by the router before dispatch.
 */
final class InputOptions extends Option
{
    public const string MAIN_KEY = 'input';
    public const string FAIL_MESSAGE_KEY = 'on-fail-message';
    public const string FAIL_REDIRECT_KEY = 'on-fail-redirect';

    public const array TYPES_IDENTIFIERS = ["arr", "num", "str"];

    /**
     * Marks one or more request fields as required.
     *
     * This functions also accepts arrays in the following form: [name, type]. This allows you to enforce a certain type
     * for your request parameters. The following types are accepted:
     * - arr for arrays
     * - num for integers/floats
     * - str for strings
     *
     * @param string|string[] $input The first required field name.
     * @param string|array ...$_
     * @return array The option array to pass to the route builder.
     */
    public static function require(string|array $input, string|array ...$_): array
    {
        $rules = [];
        $handle = function (string|array $i) use (&$rules) {
            if (is_string($i)) {
                $rules[$i] = ['required'];
            } elseif (is_array($i)) {
                if (count($i) !== 2) {
                    throw new GoralysValueError(
                        "Expected exactly two values for typed required input, got: " . count($i)
                    );
                }

                if (!in_array($i[1], self::TYPES_IDENTIFIERS)) {
                    throw new GoralysValueError(
                        "Unrecognized type identifier: " . $i[1] . ", accepted identifiers are: "
                        . implode(",", self::TYPES_IDENTIFIERS)
                    );
                }
                $rules[$i[0]] = ['required:' . $i[1]]; // format: required: type
            }
        };
        foreach ([$input, ...$_] as $v) {
            $handle($v);
        }
        return [[self::MAIN_KEY => $rules]];
    }

    /**
     * Enforces a minimum length constraint on a request field.
     * @param string $input The field name to validate.
     * @param int $min The minimum allowed length.
     * @return array The option array to pass to the route builder.
     */
    public static function min(string $input, int $min): array
    {
        $rules = [$input => ["min:$min"]];
        return [[self::MAIN_KEY => $rules]];
    }

    /**
     * Configures the error message and redirect path when input validation fails.
     * @param string $message The toast message to display on failure.
     * @param string $redirect The page to redirect the user to on failure.
     * @return array The option array to pass to the route builder.
     */
    public static function onFailure(string $message, string $redirect = "/"): array
    {
        $rules = [self::FAIL_MESSAGE_KEY => [$message], self::FAIL_REDIRECT_KEY => [$redirect]];
        return [[self::MAIN_KEY => $rules]];
    }
}
