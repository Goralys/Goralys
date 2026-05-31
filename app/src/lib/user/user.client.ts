/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { removeCookie, setCookie } from "@/app/src/lib/cookies";
import Cookies from "universal-cookie";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/app/src/lib/fetch/fetch.client";
import { UserData } from "@/app/src/lib/types";
import { EMAIL_KEY, FULL_NAME_KEY, PERSISTANT_COOKIES, PERSISTANT_LOCALS, PUB_ID_KEY, ROLE_KEY, USERNAME_KEY } from "@/app/src/lib/config";

export async function cacheUserDataClient(): Promise<void> {
    const res = await goralysFetchClient("GET", "user/profile");

    if (!res.ok) {
        return;
    }

    const data = (await res.json())["data"] as UserData;

    const cookie = new Cookies();

    setCookie(cookie, USERNAME_KEY, data.username, 1.5 * 60 * 60);
    setCookie(cookie, FULL_NAME_KEY, data.full_name, 1.5 * 60 * 60);
    setCookie(cookie, ROLE_KEY, data.role, 1.5 * 60 * 60);
    setCookie(cookie, PUB_ID_KEY, data.public_id, 1.5 * 60 * 60);
    if (data?.email) setCookie(cookie, EMAIL_KEY, data.email, 1.5 * 60 * 60);
    else removeCookie(cookie, EMAIL_KEY, 1.5 * 60 * 60);

    cookie.update();
}

export function emptyUserCacheClient(): void {
    // Invalidate old local cache
    for (let i: number = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i)!; // 'i' must be a valid index (see loop above)
        if (!PERSISTANT_LOCALS.includes(key)) localStorage.removeItem(key);
    }

    const cookies = new Cookies();

    Object.keys(cookies.getAll()).forEach((name) => {
        if (PERSISTANT_COOKIES.includes(name.trim())) return; // do not delete persistant cookies
        if (name.startsWith("__next")) return; // do not delete Next.js cookies
        cookies.remove(name, { path: "/" });
    });

    cookies.update();
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
