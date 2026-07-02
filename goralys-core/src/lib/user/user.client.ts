/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/lib/fetch/fetch.client";

let cacheUserClient: () => Promise<void>;
let emptyCacheUserClient: () => void;

export function configUserCacheCallbacks(cache: () => Promise<void>, empty: () => void): void {
    cacheUserClient = cache;
    emptyCacheUserClient = empty;
}

export async function cacheUserDataClient(): Promise<void> {
    if (!cacheUserClient) {
        throw new Error("User cache not configured. Call configUserCacheCallbacks() first.");
    }
    return await cacheUserClient();
}

export function emptyUserCacheClient(): void {
    if (!emptyCacheUserClient) {
        throw new Error("User cache not configured. Call configUserCacheCallbacks() first.");
    }
    return emptyCacheUserClient();
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
