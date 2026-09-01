/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { useCallback } from "react";
import { SupportTicket } from "@/types/support";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/lib/fetch/fetch.client";
import { SUPPORT_TICKET_CACHE, SUPPORT_TICKET_SYNC } from "@/lib/config";
import { useSyncedResource } from "@/hooks/use-synced-ressource";

export function useSupportTickets(): {
    supportTickets: SupportTicket[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const fetcher = useCallback(
        async () => await goralysFetchClient("GET", buildApiUrl("support/tickets", { "csrf-token": await fetchCsrfClient("get-tickets") })),
        [],
    );
    const parse = useCallback((data: unknown): SupportTicket[] | null => (Array.isArray(data) ? data : null), []);

    const {
        data: supportTickets,
        refetch,
        syncKey,
    } = useSyncedResource({
        name: "useSupportTickets",
        cacheKey: SUPPORT_TICKET_CACHE,
        syncKey: SUPPORT_TICKET_SYNC,
        fetcher,
        parse,
    });

    return { supportTickets, refetch, syncKey };
}
