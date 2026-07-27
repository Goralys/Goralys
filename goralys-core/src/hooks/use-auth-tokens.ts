/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { useCallback } from "react";
import { AuthToken, AuthTokenContext } from "@/types/user";
import { fetchTokensAdminPanel, fetchTokensProfile } from "@/lib/auth/fetch-auth-tokens";
import { useSyncedResource } from "@/hooks/use-synced-ressource";
import { AUTH_TOKEN_CACHES, AUTH_TOKEN_SYNCS } from "@/lib/config";

export function useAuthTokens(
    context: AuthTokenContext,
    target: string | null = null,
): {
    tokens: AuthToken[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const guard = useCallback(
        () => (context === "admin-panel" && !target ? "Cannot use admin panel context without target" : null),
        [context, target],
    );
    const fetcher = useCallback(
        async () => (context === "admin-panel" ? await fetchTokensAdminPanel(target!) : await fetchTokensProfile()),
        [context, target],
    );
    const parse = useCallback((data: unknown): AuthToken[] | null => (Array.isArray(data) ? data : null), []);

    const {
        data: tokens,
        refetch,
        syncKey,
    } = useSyncedResource({
        name: "useAuthTokens",
        cacheKey: AUTH_TOKEN_CACHES[context],
        syncKey: AUTH_TOKEN_SYNCS[context],
        fetcher,
        parse,
        guard,
    });

    return { tokens, refetch, syncKey };
}
