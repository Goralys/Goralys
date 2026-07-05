<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Shared\Lib;

use Goralys\Shared\Lib\String\StringUtils;

/**
 * Central access point for shared utility services.
 * Instantiated once by the kernel and passed down to components that need it.
 */
final readonly class GoralysLib
{
    public const string STRING = StringUtils::class;
    public StringUtils $string;

    public function __construct()
    {
        $this->string = new StringUtils();
    }
}
