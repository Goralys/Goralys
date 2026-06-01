<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Request\Interfaces;

use Goralys\Shared\Exception\Request\InvalidInputException;

/**
 * Contract that represents an incoming request.
 */
interface RequestInterface
{
    /**
     * Gets the value of an input from the request.
     * @param string $key The name of the input.
     * @return int|float|string|bool|null The value of the input.
     */
    public function param(string $key): int|float|string|bool|null;

    /**
     * Validates a given input from the request.
     * @param array $rules The rules to apply for the validation.
     * @return array The list of the validated inputs.
     * @throws InvalidInputException If the input validation fails.
     */
    public function validate(array $rules): array;
}
