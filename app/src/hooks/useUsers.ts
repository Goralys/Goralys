"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { User } from "@/app/lib/types";
import { useToast } from "@/app/ui/toast/toast-provider";
import Cookies from "universal-cookie";
import { fetchUsersClient, fetchVirtualUsersClient, fetchAdminsClient, fetchVirtualAdminsClient } from "@/app/lib/user/user.client";
import { handleToastRequest } from "@/app/lib/fetch/fetch.client";

function useUserCollection(
    fetchFn: () => Promise<Response | undefined>,
    cacheKey: string,
    syncKey: string,
): {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [users, setUsers] = useState<User[] | null>(null);
    const { showToast } = useToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const cookiesRef = useRef<Cookies>(new Cookies());
    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchUsers = useCallback(async () => {
        const cookies = cookiesRef.current;

        if (!cookies.get("username")) return;
        if (inFlightRef.current) return inFlightRef.current;

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            if (cookies.get(syncKey) == "1") {
                const cached = JSON.parse(sessionStorage.getItem(cacheKey) ?? "null");
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
                cookies.set(syncKey, "1", { path: "/" });
                sessionStorage.setItem(cacheKey, JSON.stringify(data));
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
        const cookies = new Cookies();
        const onChange = (): void => {
            if (inFlightRef.current) return;
            if (cookies.get(syncKey) != "1") void fetchUsers();
        };

        cookies.addChangeListener(onChange);
        return (): void => cookies.removeChangeListener(onChange);
    }, [fetchUsers, syncKey]);

    useEffect(() => {
        void fetchUsers();
    }, [fetchUsers]);

    return useMemo(() => ({ users, refetch: fetchUsers, syncKey }), [users, fetchUsers, syncKey]);
}

export function useUsers(): { users: User[] | null; refetch: () => Promise<void | undefined>; syncKey: string } {
    return useUserCollection(fetchUsersClient, "users-cache", "users-synced");
}

export function useVirtualUsers(): { users: User[] | null; refetch: () => Promise<void | undefined>; syncKey: string } {
    return useUserCollection(fetchVirtualUsersClient, "virtual-users-cache", "virtual-users-synced");
}

export function useAdmins(): { users: User[] | null; refetch: () => Promise<void | undefined>; syncKey: string } {
    return useUserCollection(fetchAdminsClient, "admins-cache", "admins-synced");
}

export function useVirtualAdmins(): {
    users: User[] | null;
    refetch: () => Promise<void | undefined>;
    syncKey: string;
} {
    return useUserCollection(fetchVirtualAdminsClient, "virtual-admins-cache", "virtual-admins-synced");
}
