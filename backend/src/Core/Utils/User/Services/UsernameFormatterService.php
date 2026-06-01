<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Utils\User\Services;

/**
 * The service used to format the username before sending it to the frontend.
 */
final class UsernameFormatterService
{
    /**
     * Formats a username with default format f.lastnameX into LASTNAME F. with f the first letter of the first name and
     * X a random number between 0 and 9.
     * @param string $username The username.
     * @param bool $reverse If `true`, the format will be F. LASTNAME.
     * @return string The formated result.
     */
    public static function formatUsername(string $username, bool $reverse = false): string
    {
        $username = str_replace(".admin", "", $username);
        // Expected format: first initial, dot, last name, optional digits.
        if (preg_match('/^([a-z])\.([a-z]+)\d*$/i', $username, $matches)) {
            $firstInitial = strtoupper($matches[1]);
            $lastName     = strtoupper($matches[2]);

            return $reverse ? $firstInitial . '. ' . $lastName : $lastName . ' ' . $firstInitial . '.';
        }
        // For admins
        if (preg_match('/^([a-z])\.([a-z]+).admin\d*$/i', $username, $matches)) {
            $firstInitial = strtoupper($matches[1]);
            $lastName     = strtoupper($matches[2]);

            return $reverse ? $firstInitial . '. ' . $lastName : $lastName . ' ' . $firstInitial . '.';
        }

        // Return the original value if it does not match the expected format.
        return $username;
    }
}
