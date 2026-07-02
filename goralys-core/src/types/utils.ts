/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export interface PHPDateTime {
    date: string;
    timezone_type: number;
    timezone: string;
}

export type CookieValue = string | boolean | number | null | undefined;
