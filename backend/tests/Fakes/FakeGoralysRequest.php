<?php

namespace Goralys\Tests\Fakes;

use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\Shared\Exception\Request\InvalidInputException;

final class FakeGoralysRequest implements RequestInterface
{
    private array $input = [];

    public function setInput(array $input): void
    {
        $this->input = $input;
    }

    public function param(string $key): int|float|string|bool|null
    {
        $v = $this->input[$key] ?? null;
        if (is_string($v)) {
            return trim($v);
        }
        if (is_scalar($v) || is_bool($v)) {
            return $v;
        }
        return $v;
    }

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
