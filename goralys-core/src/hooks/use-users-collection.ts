"use client";

import { useCallback } from "react";
import { User } from "@/types/user";
import { fetchAdminsClient, fetchUsersClient, fetchVirtualAdminsClient, fetchVirtualUsersClient } from "@/lib/user/user.client";
import { USER_CACHES, USER_SYNCS, UserType } from "@/lib/config";
import { useSyncedResource } from "@/hooks/use-synced-ressource";

function useUsersCollection(
    fetcher: () => Promise<Response | undefined>,
    type: UserType["type"],
): {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const parse = useCallback((data: unknown): User[] | null => (Array.isArray(data) ? data : null), []);
    const {
        data: users,
        refetch,
        syncKey,
    } = useSyncedResource({
        name: "useUsersCollection",
        cacheKey: USER_CACHES[type],
        syncKey: USER_SYNCS[type],
        fetcher,
        parse,
    });

    return { users, refetch, syncKey };
}

interface UserFetchResult {
    users: User[] | null;
    refetch: () => Promise<void | undefined>;
    syncKey: string;
}

export function useUsers(): UserFetchResult {
    return useUsersCollection(fetchUsersClient, "users-real");
}

export function useVirtualUsers(): UserFetchResult {
    return useUsersCollection(fetchVirtualUsersClient, "users-virtual");
}

export function useAdmins(): UserFetchResult {
    return useUsersCollection(fetchAdminsClient, "admins-real");
}

export function useVirtualAdmins(): UserFetchResult {
    return useUsersCollection(fetchVirtualAdminsClient, "admins-virtual");
}
