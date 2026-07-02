"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { User } from "@/types/user";
import { fetchAdminsClient, fetchUsersClient, fetchVirtualAdminsClient, fetchVirtualUsersClient } from "@/lib/user/user.client";
import { handleToastRequest } from "@/lib/fetch/fetch.client";
import { USER_CACHES, USER_SYNCS, UserType } from "@/lib/config";
import { ToastFn } from "@/types/toast";
import { storageGet, storageSet } from "@/lib/storage/storage-adapter";
import { isAuthenticated } from "@/lib/auth/check-auth";

function useUserCollection(
    fetchFn: () => Promise<Response | undefined>,
    type: UserType["type"],
    showToast: ToastFn,
): {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const syncKey = USER_SYNCS[type];
    const cacheKey = USER_CACHES[type];
    const [users, setUsers] = useState<User[] | null>(null);
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchUsers = useCallback(async () => {
        if (!isAuthenticated()) {
            console.log("[useUsers] not authenticated, aborting");
            return;
        }

        if (inFlightRef.current) return inFlightRef.current;

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            if ((await storageGet(syncKey)) == "1") {
                const cached = JSON.parse((await storageGet(cacheKey)) ?? "null");
                setUsers((prev) => {
                    if (JSON.stringify(prev) === JSON.stringify(cached)) return prev;
                    return cached;
                });
                return;
            }

            const res = await fetchFn();
            if (res) await handleToastRequest(res, showToastRef.current, false);
            const data = await res?.json();

            if (res?.ok) {
                await storageSet(syncKey, "1");
                await storageSet(cacheKey, JSON.stringify(data));
            }

            const result = Array.isArray(data) ? (data as User[]) : null;
            setUsers((prev) => {
                if (JSON.stringify(prev) === JSON.stringify(result)) return prev;
                return result;
            });
        } finally {
            inFlightRef.current = null;
            resolve!();
        }
    }, [fetchFn, cacheKey, syncKey]);

    useEffect(() => {
        void fetchUsers();
    }, [fetchUsers]);

    return useMemo(() => ({ users, refetch: fetchUsers, syncKey }), [users, fetchUsers, syncKey]);
}

interface UserFetchResult {
    users: User[] | null;
    refetch: () => Promise<void | undefined>;
    syncKey: string;
}

export function useUsers(showToast: ToastFn): UserFetchResult {
    return useUserCollection(fetchUsersClient, "users-real", showToast);
}

export function useVirtualUsers(showToast: ToastFn): UserFetchResult {
    return useUserCollection(fetchVirtualUsersClient, "users-virtual", showToast);
}

export function useAdmins(showToast: ToastFn): UserFetchResult {
    return useUserCollection(fetchAdminsClient, "admins-real", showToast);
}

export function useVirtualAdmins(showToast: ToastFn): UserFetchResult {
    return useUserCollection(fetchVirtualAdminsClient, "admins-virtual", showToast);
}
