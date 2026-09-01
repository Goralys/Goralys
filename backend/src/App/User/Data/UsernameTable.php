<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Data;

use Goralys\App\Config\AppConfig;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\User\GoralysUserException;
use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\Lib\String\StringCase;

final class UsernameTable
{
    /** @var array<string, string> */
    private array $table = [];
    /** @var array<string, string> */
    private array $reverse = [];

    public function __construct()
    {
        if (!file_exists(Config::USER::USERNAME_LIST_PATH)) {
            mkdir(dirname(Config::USER::USERNAME_LIST_PATH), recursive: true);
            file_put_contents(Config::USER::USERNAME_LIST_PATH, "");
            return;
        }

        $raw = file(Config::USER::USERNAME_LIST_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($raw as $line) {
            $parts = explode("=", $line);
            if (count($parts) !== 2) {
                continue;
            }

            [$fullName, $username] = $parts;
            $this->table[$fullName] = $username;
            $this->reverse[$username] = $fullName;
        }
    }

    /**
     * Returns the username for a full name, generating and caching it if needed.
     * @param string $fullName The fullname of the user
     * @param UserRole $role The user's role. This is only specified when creating admin to add a special suffix.
     * @return string The username
     * @throws GoralysUserException If the generation fails.
     */
    public function resolve(string $fullName, UserRole $role = UserRole::UNKNOWN): string
    {
        $suffix = $role === UserRole::ADMIN ? Config::USER::ADMIN_SUFFIX : "";
        if (isset($this->table[$fullName . $suffix])) {
            return $this->table[$fullName . $suffix];
        }
        [$firstNameParts, $lastNameParts] = Lib::STRING::separateNames($fullName, true);

        $firstName = implode("", $firstNameParts);
        $lastName = "";
        for ($i = 0; $i < count($lastNameParts); $i++) {
            $lastName .= $lastNameParts[$i];
            if (!in_array($lastNameParts[$i], AppConfig::NAME_PARTICULES)) {
                break;
            }
        }

        $firstName = Lib::STRING::sanitize($firstName, StringCase::LOWER);
        $lastName = str_replace(["'"], [""], Lib::STRING::sanitize(
            explode("-", $lastName)[0],
            StringCase::LOWER,
        ));
        $base = Lib::STRING::sanitize(
            substr($firstName, 0, 1) . "." . $lastName . $suffix,
            StringCase::LOWER,
        );
        $number = rand(0, 9);

        // Test all 10 possibilities.
        $found = false;
        for ($i = 0; $i < 10; $i++) {
            if (!isset($this->reverse[$base . (($number + $i) % 10)])) {
                $number = ($number + $i) % 10;
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new GoralysUserException("To many users with username base: $base");
        }
        $username = $base . $suffix . $number;
        file_put_contents(
            Config::USER::USERNAME_LIST_PATH,
            PHP_EOL . $fullName . $suffix . "=" . $username,
            FILE_APPEND
        );
        $this->reverse[$username] = $fullName . $suffix;
        return $this->table[$fullName . $suffix] = $username;
    }

    public function remove(string $username): bool
    {
        if (!isset($this->reverse[$username])) {
            return false;
        }

        $fullName = $this->reverse[$username];

        $raw = file(Config::USER::USERNAME_LIST_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$raw) {
            return false;
        }

        $lineNumber = null;
        foreach ($raw as $i => $line) {
            [$f, $u] = array_map('trim', explode("=", $line)); // f = fullName, u = username
            if ($u === $username && $f === $fullName) {
                $lineNumber = $i;
                break;
            }
        }

        if ($lineNumber === null) {
            return false;
        }

        unset($raw[$lineNumber]);
        if (
            !file_put_contents(Config::USER::USERNAME_LIST_PATH, $raw
            |> array_values(...)
            |> (fn($x) => implode(PHP_EOL, $x)))
        ) {
            return false;
        }

        unset($this->table[$fullName]);
        unset($this->reverse[$fullName]);
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->table;
    }
}
