<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Core\User\Data\Enums\UserRole;

/**
 * The DTO used to append a new user to the database
 */
final readonly class UserCreateDTO
{
    /**
     * @param string $username The username of the new user.
     * @param string $passwordHash The hashed password of the new user.
     * @param UserRole $role The role assigned to the new user.
     */
    public function __construct(
        public string $username,
        public string $passwordHash,
        public UserRole $role,
    ) {
    }
}
