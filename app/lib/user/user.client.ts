/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { removeCookie, setCookie } from "@/app/lib/cookies";
import Cookies from "universal-cookie";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { UserData } from "@/app/lib/types";
import { PERSISTANT_COOKIES } from "@/app/lib/config";

export async function cacheUserDataClient(): Promise<void> {
    const res = await goralysFetchClient("user/profile", { method: "GET" });

    if (!res.ok) {
        return;
    }

    const data = (await res.json())["data"] as UserData;

    const cookie = new Cookies();

    setCookie(cookie, "username", data.username, 1.5 * 60 * 60);
    setCookie(cookie, "full-name", data.full_name, 1.5 * 60 * 60);
    setCookie(cookie, "user-role", data.role, 1.5 * 60 * 60);
    setCookie(cookie, "public-id", data.public_id, 1.5 * 60 * 60);
    if (data?.email) setCookie(cookie, "email", data.email, 1.5 * 60 * 60);
    else removeCookie(cookie, "email", 1.5 * 60 * 60);

    cookie.update();
}

export function emptyUserCacheClient(): void {
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

    return await goralysFetchClient(buildApiUrl("users/all", { "csrf-token": csrfToken }, false), {
        method: "GET",
    });
}

export async function fetchVirtualUsersClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-users");

    return await goralysFetchClient(buildApiUrl("users/virtual", { "csrf-token": csrfToken }, false), {
        method: "GET",
    });
}

export async function fetchAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-all-admins");

    return await goralysFetchClient(buildApiUrl("admins/all", { "csrf-token": csrfToken }, false), {
        method: "GET",
    });
}

export async function fetchVirtualAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-admins");

    return await goralysFetchClient(buildApiUrl("admins/virtual", { "csrf-token": csrfToken }, false), {
        method: "GET",
    });
}
