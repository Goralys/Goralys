<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

/**
 * The DTO used to log in a user
 */
final readonly class UserLoginDTO
{
    /**
     * @param string $username The username of the user attempting to log in.
     * @param string $password The plain-text password provided by the user.
     */
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
