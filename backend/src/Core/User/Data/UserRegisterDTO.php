<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Shared\Lib\GoralysLib as Lib;

/**
 * The DTO used to register a user
 */
final class UserRegisterDTO
{
    /**
     * @param string $username The desired username for the new account.
     * @param string $password The plain-text password (to be hashed before storage).
     * @param ?string $email The email of the user (optionnal).
     */
    public function __construct(
        private(set) string $username,
        public readonly string $password,
        public readonly ?string $email,
    ) {
    }

    /**
     * This helper sanitizes the data inside the DTO as they come from user input.
     * @return void
     */
    public function sanitize(): void
    {
        $this->username = Lib::STRING::sanitize($this->username);
    }
}
