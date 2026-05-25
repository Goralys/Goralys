<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Core\User\Data\Enums\UserRole;

/**
 * The DTO containing all the information of a user
 */
final readonly class UserFullDTO
{
    /**
     * @param int $id The unique database ID of the user.
     * @param string $username The username of the user.
     * @param UserRole $role The role of the user.
     * @param string $fullName The full name of the user.
     * @param string $email The email of the user.
     */
    public function __construct(
        public int $id,
        public string $username,
        public UserRole $role,
        public string $fullName,
        public string $email,
    ) {}
}
