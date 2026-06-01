<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data;

/**
 * DTO used to represent "technological" ways
 */
final readonly class PathwayDTO
{
    /**
     * @param string $full The full name of the pathway.
     * @param string $detectPattern The pattern used to detect this pathway.
     */
    public function __construct(
        public string $full,
        public string $detectPattern,
    ) {
    }
}
