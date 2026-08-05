/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/lib/fetch/fetch.client";
import { UserData } from "@/types/user";
import { EMAIL_KEY, FULL_NAME_KEY, PERSISTANT_COOKIES, PERSISTANT_LOCALS, PUB_ID_KEY, ROLE_KEY, USERNAME_KEY } from "@/lib/config";
import { storageKeyAt, storageRemove, storageSize } from "@/lib/storage/storage-adapter";
import { cookiesGetAll, cookiesRemove, cookiesSet } from "@/lib/storage/cookies-adapter";

export async function cacheUserDataClient(): Promise<void> {
    const res = await goralysFetchClient("GET", "user/profile");

    if (!res.ok) {
        return;
    }

    const data = (await res.json())["data"] as UserData;

    cookiesSet(USERNAME_KEY, data.username, "/", 1.5 * 60 * 60);
    cookiesSet(FULL_NAME_KEY, data.fullName, "/", 1.5 * 60 * 60);
    cookiesSet(ROLE_KEY, data.role, "/", 1.5 * 60 * 60);
    cookiesSet(PUB_ID_KEY, data.publicId, "/", 1.5 * 60 * 60);
    if (data?.email) cookiesSet(EMAIL_KEY, data.email, "/", 1.5 * 60 * 60);
    else cookiesRemove(EMAIL_KEY);
}

export function emptyUserCacheClient(): void {
    // Invalidate old local cache
    for (let i: number = 0; i < storageSize(); i++) {
        const key = storageKeyAt(i)!; // 'i' must be a valid index (see loop above)
        if (!PERSISTANT_LOCALS.includes(key)) storageRemove(key);
    }

    Object.keys(cookiesGetAll()).forEach((name) => {
        if (PERSISTANT_COOKIES.includes(name.trim())) return; // do not delete persistant cookies
        if (name.startsWith("__next")) return; // do not delete Next.js cookies
        cookiesRemove(name);
    });
}

export async function fetchUsersClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-all-users");

    return await goralysFetchClient("GET", buildApiUrl("users/all", { "csrf-token": csrfToken }));
}

export async function fetchVirtualUsersClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-users");

    return await goralysFetchClient("GET", buildApiUrl("users/virtual", { "csrf-token": csrfToken }));
}

export async function fetchAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-all-admins");

    return await goralysFetchClient("GET", buildApiUrl("admins/all", { "csrf-token": csrfToken }));
}

export async function fetchVirtualAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-admins");

    return await goralysFetchClient("GET", buildApiUrl("admins/virtual", { "csrf-token": csrfToken }));
}
