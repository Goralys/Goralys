<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Config;

use Goralys\App\Config\Data\RateLimit;
use Goralys\App\Config\Data\RateLimitTimeMethod;

/**
 * Configuration for the rate limiter.
 * Defines the general fallback limits as well as per-endpoint rules.
 */
final class RateLimiterConfig
{
    public const array GENERAL = [100, 60];

    /**
     * @return array<string, RateLimit>
     */
    public static function getRateLimits(): array
    {
        return [
            'login' => new RateLimit(3, 60, RateLimitTimeMethod::EXPONENTIAL, 5),
            'register' => new RateLimit(5, 180, RateLimitTimeMethod::LINEAR, 3),
            'flash-toast' => new RateLimit(100, 60, RateLimitTimeMethod::CONSTANT),
            'csrf-create' => new RateLimit(30, 60, RateLimitTimeMethod::CONSTANT),

            'admin-panel' => new RateLimit(3, 90, RateLimitTimeMethod::CONSTANT),
            'admin-fetch' => new RateLimit(15, 90, RateLimitTimeMethod::CONSTANT),

            'support-ticker' => new RateLimit(1, 60 * 10, RateLimitTimeMethod::CONSTANT),
        ];
    }
}
