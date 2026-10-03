<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger\Data\Enums;

/**
 * An enum used to represent the layer of a log.
 */
enum LoggerInitiator: string
{
    case API = "API";
    case CRON = "CRON";

    case APP = "APP";
    case CORE = "CORE";
    case PLATFORM = "PLATFORM";
    case KERNEL = "KERNEL";

    public function toString(): string
    {
        return match ($this) {
            LoggerInitiator::API => "API",
            LoggerInitiator::CRON => "CronJob",

            LoggerInitiator::APP => "App",
            LoggerInitiator::CORE => "Core",
            LoggerInitiator::PLATFORM => "Platform",
            LoggerInitiator::KERNEL => "Kernel",
        };
    }
}
