<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Router\Options;

/**
 * Builder for route input-validation option arrays.
 * The produced arrays are merged into a route's options and processed by the router before dispatch.
 */
final class InputOptions extends Option
{
    public const string MAIN_KEY = 'input';
    public const string FAIL_MESSAGE_KEY = 'on-fail-message';
    public const string FAIL_REDIRECT_KEY = 'on-fail-redirect';

    /**
     * Marks one or more request fields as required.
     * @param string $input The first required field name.
     * @param string ...$_ Additional required field names.
     * @return array The option array to pass to the route builder.
     */
    public static function require(string $input, string ...$_): array
    {
        $rules = [$input => ['required']];
        foreach ($_ as $v) {
            $rules[$v] = ['required'];
        }
        return [['input' => $rules]];
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
        return [['input' => $rules]];
    }

    /**
     * Configures the error message and redirect path when input validation fails.
     * @param string $message The toast message to display on failure.
     * @param string $redirect The page to redirect the user to on failure.
     * @return array The option array to pass to the route builder.
     */
    public static function onFailure(string $message, string $redirect = "/"): array
    {
        $rules = [self::FAIL_MESSAGE_KEY => $message, self::FAIL_REDIRECT_KEY => $redirect];
        return [['input' => $rules]];
    }
}
