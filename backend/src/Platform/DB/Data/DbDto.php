<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\DB\Data;

/**
 * Main Database DTO.
 * It's internal to the DB layer and contains the credentials to log in to the database.
 */
final readonly class DbDto
{
    public function __construct(
        public string $host,
        public string $name,
        public string $username,
        public string $password,
    ) {
    }
}
