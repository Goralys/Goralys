<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Config\Data;

/**
 * DTO holding the configuration for a single rate-limit rule.
 */
final readonly class RateLimit
{
    public function __construct(
        public int $maxRequests,
        public int $timeWindowSeconds,
        public RateLimitTimeMethod $timeMethod = RateLimitTimeMethod::CONSTANT,
        public int $maxLevels = 1,
    ) {
    }
}
