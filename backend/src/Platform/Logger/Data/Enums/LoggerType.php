<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger\Data\Enums;

/**
 * An enum used to represent the type of log.
 */
enum LoggerType: int
{
    // Verbosity levels (higher = more severe).
    // Debug and Info are both 1XX because they do not differ that much from each other.
    case Debug = 100;
    case Info = 110;
    case Warning = 200;
    case Error = 300;
    case Fatal = 400;

    public function toString(): string
    {
        return match ($this) {
            LoggerType::Debug => "DEBUG",
            LoggerType::Info => "INFO",
            LoggerType::Warning => "WARNING",
            LoggerType::Error => "ERROR",
            LoggerType::Fatal => "FATAL",
        };
    }
}
