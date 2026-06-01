"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { User } from "@/app/src/lib/types";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import Cookies from "universal-cookie";
import { fetchAdminsClient, fetchUsersClient, fetchVirtualAdminsClient, fetchVirtualUsersClient } from "@/app/src/lib/user/user.client";
import { handleToastRequest } from "@/app/src/lib/fetch/fetch.client";
import { USER_CACHES, USER_SYNCS, USERNAME_KEY, UserType } from "@/app/src/lib/config";

function useUserCollection(
    fetchFn: () => Promise<Response | undefined>,
    type: UserType["type"],
): {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const syncKey = USER_SYNCS[type];
    const cacheKey = USER_CACHES[type];
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

        if (!cookies.get(USERNAME_KEY)) return;
        if (inFlightRef.current) return inFlightRef.current;

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            if (cookies.get(syncKey) == "1") {
                const cached = JSON.parse(localStorage.getItem(cacheKey) ?? "null");
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
                localStorage.setItem(cacheKey, JSON.stringify(data));
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
    return useUserCollection(fetchUsersClient, "users-real");
}

export function useVirtualUsers(): { users: User[] | null; refetch: () => Promise<void | undefined>; syncKey: string } {
    return useUserCollection(fetchVirtualUsersClient, "users-virtual");
}

export function useAdmins(): { users: User[] | null; refetch: () => Promise<void | undefined>; syncKey: string } {
    return useUserCollection(fetchAdminsClient, "admins-real");
}

export function useVirtualAdmins(): {
    users: User[] | null;
    refetch: () => Promise<void | undefined>;
    syncKey: string;
} {
    return useUserCollection(fetchVirtualAdminsClient, "admins-virtual");
}
