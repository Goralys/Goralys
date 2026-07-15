<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Context;

use Goralys\App\Context\Data\ToastMode;

/**
 * Holds the application-level context shared across the request lifecycle.
 */
final class AppContext
{
    public function __construct(
        public ToastMode $mode,
        public string $originDomain,
        public bool $isDev,
    ) {
    }
}
