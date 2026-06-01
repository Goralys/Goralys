<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use Goralys\Core\User\Data\Enums\UserRole;

final readonly class VirtualUserDTO
{
    /**
     * @param string $username The username of the user.
     * @param UserRole $role The role of the user.
     */
    public function __construct(
        public string $username,
        public UserRole $role,
    ) {
    }
}
