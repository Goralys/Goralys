/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export interface GoralysClientConfig {
    apiDomain: string;
}

let config: GoralysClientConfig | null = null;

export function configureGoralysClient(cfg: GoralysClientConfig): void {
    config = cfg;
}

export function getGoralysClientConfig(): GoralysClientConfig {
    if (!config) {
        throw new Error("Goralys client not configured. Call configureGoralysClient() first.");
    }
    return config;
}
