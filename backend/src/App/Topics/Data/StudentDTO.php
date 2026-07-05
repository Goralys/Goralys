<?php

namespace Goralys\App\Topics\Data;

use Goralys\Shared\User\Data\FullNameDTO;

final readonly class StudentDTO
{
    public function __construct(
        public FullNameDTO $fullName,
        public string $classroom
    ) {
    }

    public function __toString(): string
    {
        return $this->classroom . "|" . $this->fullName;
    }
}
