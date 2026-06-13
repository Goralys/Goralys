<?php

namespace Goralys\Shared\User\Data;

/**
 * This DTO is used to easily manipulate full names across the system.
 */
readonly class FullNameDTO
{
    /**
     * @param string $first The frist name.
     * @param string $last The last name.
     */
    public function __construct(
        public string $first,
        public string $last,
    ) {
    }

    /**
     * Returns a new full name in reversed order. This is used to reverse the format order in certain context.
     * @return self The reversed full name.
     */
    public function reversed(): self
    {
        return new self($this->last, $this->first);
    }

    public function __toString(): string
    {
        return implode(" ", [$this->first, $this->last]);
    }
}
