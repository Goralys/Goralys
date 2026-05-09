/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { UserEvent } from "@/app/lib/types";

const listeners = new Set<(event: UserEvent) => void>();

export function emitUserEvent(event: UserEvent): void {
    listeners.forEach((l) => l(event));
}

export function onUserEvent(callback: (event: UserEvent) => void) {
    listeners.add(callback);
    return (): void => {
        listeners.delete(callback);
    };
}
