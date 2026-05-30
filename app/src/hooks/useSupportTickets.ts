/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { SupportTicket } from "@/app/lib/types";
import { useToast } from "@/app/ui/toast/toast-provider";
import Cookies from "universal-cookie";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, handleToastRequest } from "@/app/lib/fetch/fetch.client";

export function useSupportTickets(): {
    supportTickets: SupportTicket[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [supportTickets, setSupportTickets] = useState<SupportTicket[] | null>(null);
    const { showToast } = useToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const cookiesRef = useRef<Cookies>(new Cookies());

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchSupportTickets = useCallback(async () => {
        const cookies = cookiesRef.current;
        const cacheKey = `support-tickets-cache`;
        const syncKey = `support-tickets-synced`;

        if (!cookies.get("username")) {
            console.log("[useSupportTickets] no username cookie, aborting");
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
            const syncValue = cookies.get(syncKey);

            if (syncValue == "1") {
                const raw = localStorage.getItem(cacheKey);
                if (raw === null || raw === undefined) {
                    cookies.set(syncKey, "0", { path: "/" });
                    localStorage.removeItem(cacheKey);
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
                buildApiUrl("support/tickets", { "csrf-token": await fetchCsrfClient("get-tickets") }, false),
                {
                    method: "GET",
                },
            );
            if (res) await handleToastRequest(res, showToastRef.current, false);
            const data = await res?.json();

            cookies.set(syncKey, "1", { path: "/" });
            localStorage.setItem(cacheKey, JSON.stringify(data));
            console.log("[useSupportTickets] set syncKey and cached to localStorage");
            console.log("[useSupportTickets] localStorage after set:", localStorage.getItem(cacheKey)?.slice(0, 100));

            const result = Array.isArray(data) ? data : null;
            console.log("[useSupportTickets] setting supportTickets:", result ? `array(${result.length})` : result);
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
        const cookies = new Cookies();
        const onChange = (): void => {
            if (inFlightRef.current) return;
            const syncKey = `support-tickets-synced`;
            if (cookies.get(syncKey) != "1") {
                void fetchSupportTickets();
            }
        };

        cookies.addChangeListener(onChange);
        return (): void => cookies.removeChangeListener(onChange);
    }, [fetchSupportTickets]);

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
