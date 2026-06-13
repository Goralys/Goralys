<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Shared\User\Data\FullNameDTO;

/**
 * The DTO used to register a user
 */
final readonly class UserRegisterDTO
{
    /**
     * @param string $username The desired username for the new account.
     * @param FullNameDTO $fullName The full name of the registering user.
     * @param string $password The plain-text password (to be hashed before storage).
     */
    public function __construct(
        public string $username,
        public FullNameDTO $fullName,
        public string $password,
    ) {
    }
}
