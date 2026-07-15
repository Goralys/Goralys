<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Request;

use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\Shared\Exception\Request\InvalidInputException;

/**
 * A simple class used to easily access an HTTP request's inputs
 */
final class GoralysRequest implements RequestInterface
{
    private array $input;

    public function __construct()
    {
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);

        // POST/body initialization
        if (!empty($_POST)) {
            $this->input = $_POST;
        } elseif (is_array($json)) {
            $this->input = $json;
        } else {
            $this->input = [];
        }

        // GET requests
        if (!empty($_GET)) {
            $this->input = array_merge($this->input, $_GET);
        }
    }


    /**
     * Reads the inputs of the request and returns the desired value.
     * By default, it falls back to `null` if the input doesn't exit.
     * @param string $key The name of the input to read.
     * @return int|float|string|bool|null The value of the input.
     */
    public function param(string $key): int|float|string|bool|null
    {
        $v = $this->input[$key] ?? null;
        if (is_string($v)) {
            return trim($v);
        }
        return $v;
    }

    /**
     * Gets the header of the request.
     * @param string $key The name of the header.
     * @return ?string The headers of the request.
     */
    public function header(string $key): ?string
    {
        return $_SERVER[$key] ?? null;
    }

    /**
     * Check if a request's input is valid.
     * @param array $rules The rules to apply for the validation.
     * @return array The validated inputs' data.
     * @throws InvalidInputException If the input validation fails.
     */
    public function validate(array $rules): array
    {
        $validated = [];

        foreach ($rules as $k => $constraints) {
            $value = $this->input[$k] ?? null;

            foreach ($constraints as $constraint) {
                if ($constraint === 'required') {
                    if (!array_key_exists($k, $this->input)) {
                        throw new InvalidInputException("$k is required");
                    }

                    if (is_string($value) && trim($value) === '') {
                        throw new InvalidInputException("$k cannot be empty");
                    }

                    if ($value === null) {
                        throw new InvalidInputException("$k is required");
                    }
                }

                if (str_starts_with($constraint, 'min')) {
                    $min = (int) explode(":", $constraint)[1];
                    if (strlen($value) < $min) {
                        throw new InvalidInputException("$k is too short (min $min)");
                    }
                }
            }

            $validated[$k] = $value;
        }

        return $validated;
    }
}
