/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { storageSet } from "@/lib/storage/storage-adapter";

export function setCookiesExpire(duration: number): void {
    storageSet("goralys-cookies-expire", String(Date.now() + duration * 1000));
}
