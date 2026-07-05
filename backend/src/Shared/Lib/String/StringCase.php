<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Shared\Lib\String;

/**
 * Defines the casing transformation to apply when sanitizing or comparing strings.
 */
enum StringCase
{
    case NONE;
    case UPPER;
    case LOWER;
}
