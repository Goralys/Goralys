/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { SupportTicket } from "@/types/support";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/lib/fetch/fetch.client";
import { SUPPORT_TICKET_CACHE, SUPPORT_TICKET_SYNC } from "@/lib/config";
import { storageGet, storageRemove, storageSet } from "@/lib/storage/storage-adapter";
import { ToastFn } from "@/types/toast";
import { isAuthenticated } from "@/lib/auth/check-auth";

export function useSupportTickets(showToast: ToastFn): {
    supportTickets: SupportTicket[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [supportTickets, setSupportTickets] = useState<SupportTicket[] | null>(null);
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchSupportTickets = useCallback(async () => {
        const syncKey = SUPPORT_TICKET_SYNC;
        const cacheKey = SUPPORT_TICKET_CACHE;

        if (!isAuthenticated()) {
            console.log("[useSupportTickets] not authenticated, aborting");
            return;
        }

        if (inFlightRef.current) {
            console.log("[useSupportTickets] in-flight, waiting");
            return inFlightRef.current;
        }

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            const syncValue = (await storageGet(syncKey)) ?? "0";

            if (syncValue == "1") {
                const raw = await storageGet(cacheKey);
                if (raw === null || raw === undefined) {
                    await storageSet(syncKey, "0");
                    await storageRemove(cacheKey);
                    await fetchSupportTickets();
                    return;
                }
                const cached = JSON.parse(raw ?? "null");
                setSupportTickets((prev) => {
                    if (JSON.stringify(prev) === JSON.stringify(cached)) return prev;
                    return cached;
                });
                return;
            }

            const res = await goralysFetchClient(
                "GET",
                buildApiUrl("support/tickets", { "csrf-token": await fetchCsrfClient("get-tickets") }),
            );
            if (res) await handleToastRequest(res, showToastRef.current, false);
            const data = await res?.json();

            await storageSet(syncKey, "1");
            await storageSet(cacheKey, JSON.stringify(data));
            console.log("[useSupportTickets] set syncKey and cached to localStorage");

            const result = Array.isArray(data) ? data : null;
            setSupportTickets((prev) => {
                if (JSON.stringify(prev) === JSON.stringify(result)) return prev;
                return result;
            });
        } finally {
            inFlightRef.current = null;
            resolve!();
        }
    }, []);

    useEffect(() => {
        void fetchSupportTickets();
    }, [fetchSupportTickets]);

    return useMemo(
        () => ({
            supportTickets,
            refetch: fetchSupportTickets,
            syncKey: `support-tickets-synced`,
        }),
        [supportTickets, fetchSupportTickets],
    );
}
