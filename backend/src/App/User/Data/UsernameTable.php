<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Data;

use Goralys\App\Config\AppConfig;
use Goralys\Shared\Exception\User\GoralysUserException;
use Goralys\Shared\Utils\String\Data\StringCase;
use Goralys\Shared\Utils\UtilitiesManager;

final class UsernameTable
{
    /** @var array<string, string> */
    private array $table = [];

    public function __construct(private readonly UtilitiesManager $utils) {}

    /**
     * Returns the username for a full name, generating and caching it if needed.
     * @throws GoralysUserException If the generation fails.
     */
    public function resolve(string $fullName): string
    {
        if (isset($this->table[$fullName])) {
            return $this->table[$fullName];
        }

        $fullName = trim($fullName);
        $names = explode(" ", $fullName);
        $lastNameParts = array_values(array_filter($names, fn($n) => strtoupper($n) === $n));
        $firstNameParts = array_values(array_filter($names, fn($n) => strtoupper($n) !== $n));

        $firstName = implode("", $firstNameParts);
        $lastName = "";
        for ($i = 0; $i < count($lastNameParts); $i++) {
            $lastName .= $lastNameParts[$i];
            if (!in_array($lastNameParts[$i], AppConfig::NAME_PARTICULES)) {
                break;
            }
        }

        $firstName = $this->utils->string->sanitize($firstName, StringCase::LOWER);
        $lastName = str_replace(["'"], [""], $this->utils->string->sanitize(
            explode("-", $lastName)[0],
            StringCase::LOWER,
        ));
        $base = $this->utils->string->sanitize(
            substr($firstName, 0, 1) . "." . $lastName,
            StringCase::LOWER,
        );
        $number = rand(0, 9);

        // Test all 10 possibilities.
        $found = false;
        for ($i = 0; $i < 10; $i++) {
            if (!in_array($base . (($number + $i) % 10), array_values($this->table))) {
                $number = ($number + $i) % 10;
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new GoralysUserException("To many users with username base: $base");
        }

        return $this->table[$fullName] = $base . $number;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->table;
    }
}
