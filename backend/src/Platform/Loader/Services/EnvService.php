<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Loader\Services;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidEncodingException;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;

/**
 * A simple wrapper around {@see DotEnv} to load the environment variables
 */
final class EnvService
{
    /**
     * Load the environment variables inside $_ENV
     * @param string $path The path to the .env file
     * @return bool `true` if the loading was successful, `false` elsewise
     */
    public function load(string $path): bool
    {
        try {
            $env = Dotenv::createImmutable($path); // Load the .env file inside the project root
            $env->load();
        } catch (InvalidPathException | InvalidFileException | InvalidEncodingException | ValidationException) {
            return false;
        }
        return true;
    }

    /**
     * Returns the environment value for the specified key
     * @param string $key The environment variable to get
     * @return mixed The environment variable value
     */
    public function getByKey(string $key): mixed
    {
        if (!array_key_exists($key, $_ENV)) {
            return null;
        }

        $value = $_ENV[$key];

        if ($value == "") {
            return null;
        }

        return $value;
    }
}
