"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { handleToastRequest } from "@/lib/fetch/fetch.client";
import { isAuthenticated } from "@/lib/auth/check-auth";
import { storageGet, storageRemove, storageSet } from "@/lib/storage/storage-adapter";
import { cookiesGet, cookiesOnChange, cookiesSet } from "@/lib/storage/cookies-adapter";
import { getToastConfig } from "@/lib/toast/config";

export interface UseSyncedResourceOptions<T> {
    name: string;
    cacheKey: string;
    syncKey: string;
    fetcher: () => Promise<Response | null | undefined>;
    parse: (data: unknown) => T | null;
    requireAuth?: boolean;
    guard?: () => string | null | undefined;
}

/**
 * Shared implementation behind useSubjects / useAuthTokens: cookies sync, localStorage cache, single-flight de-duplication, and a cross-tab
 * `cookiesOnChange` listener that re-fetches once another tab invalidates the sync flag.
 */
export function useSyncedResource<T>({ name, cacheKey, syncKey, fetcher, parse, requireAuth = true, guard }: UseSyncedResourceOptions<T>): {
    data: T | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [data, setData] = useState<T | null>(null);
    const showToast = getToastConfig().getShowToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchResource = useCallback(async () => {
        const abortReason = guard?.();
        if (abortReason) {
            console.error(`[${name}] ${abortReason}`);
            return;
        }

        if (requireAuth && !isAuthenticated()) {
            return;
        }

        if (inFlightRef.current) {
            return inFlightRef.current;
        }

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            const syncValue = cookiesGet(syncKey);

            if (syncValue == "1") {
                const raw = storageGet(cacheKey);
                if (raw === null || raw === undefined) {
                    cookiesSet(syncKey, "0");
                    storageRemove(cacheKey);
                    inFlightRef.current = null;
                    resolve!();
                    await fetchResource();
                    return;
                }
                const cached = JSON.parse(raw ?? "null");
                setData((prev) => (JSON.stringify(prev) === JSON.stringify(cached) ? prev : cached));
                return;
            }

            const res = await fetcher();
            if (res) await handleToastRequest(res, showToastRef.current, false);
            const raw = await res?.json();

            cookiesSet(syncKey, "1");
            storageSet(cacheKey, JSON.stringify(raw));

            const result = parse(raw);
            setData((prev) => (JSON.stringify(prev) === JSON.stringify(result) ? prev : result));
        } finally {
            inFlightRef.current = null;
            resolve!();
        }
    }, [cacheKey, syncKey, fetcher, parse, requireAuth, guard, name]);

    useEffect(() => {
        const onChange = (): void => {
            if (inFlightRef.current) return;
            if (cookiesGet(syncKey) != "1") {
                void fetchResource();
            }
        };

        return cookiesOnChange(onChange);
    }, [fetchResource, syncKey]);

    useEffect(() => {
        void fetchResource();
    }, [fetchResource]);

    return useMemo(() => ({ data, refetch: fetchResource, syncKey }), [data, fetchResource, syncKey]);
}
