<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\User\Data\FullNameDTO;

/**
 * The DTO used to register a user
 */
final class UserRegisterDTO
{
    /**
     * @param string $username The desired username for the new account.
     * @param FullNameDTO $fullName The full name of the registering user.
     * @param string $password The plain-text password (to be hashed before storage).
     */
    public function __construct(
        private(set) string $username,
        private(set) FullNameDTO $fullName,
        public readonly string $password,
    ) {
    }

    /**
     * This helper sanitizes the data inside the DTO as they come from user input.
     * @return void
     */
    public function sanitize(): void
    {
        $this->username = Lib::STRING::sanitize($this->username);
        $this->fullName = new FullNameDTO(
            Lib::STRING::sanitize($this->fullName->first),
            Lib::STRING::sanitize($this->fullName->last)
        );
    }
}
