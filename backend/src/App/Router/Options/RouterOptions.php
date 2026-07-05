<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Router\Options;

/**
 * Entry point for accessing route option builder classes.
 */
final class RouterOptions
{
    public static string $INPUT = InputOptions::class;
    public static string $TOAST = ToastOptions::class;
}
