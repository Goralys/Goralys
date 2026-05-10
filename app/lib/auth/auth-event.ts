/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { AuthEvent } from "@/app/lib/types";

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
