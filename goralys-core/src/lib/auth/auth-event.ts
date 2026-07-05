/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { AuthEvent } from "@/types/user";

const listeners = new Set<(event: AuthEvent) => void>();

export function emitAuthEvent(event: AuthEvent): void {
    listeners.forEach((l) => l(event));
}

export function onAuthEvent(callback: (event: AuthEvent) => void) {
    listeners.add(callback);
    return (): void => {
        listeners.delete(callback);
    };
}
