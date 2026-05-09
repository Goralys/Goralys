/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { setCookie } from "@/app/lib/cookies";
import Cookies from "universal-cookie";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { UserData } from "@/app/lib/types";

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
}

export function emptyUserCacheClient(): void {
    const cookies = new Cookies();

    Object.keys(cookies.getAll()).forEach((name) => {
        if (name == "cookie-banner-dismissed") return;
        cookies.remove(name, { path: "/" });
    });

    cookies.update();
}

export async function fetchUsersClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-all-users");
    const payload = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("users/all", {
        method: "POST",
        body: JSON.stringify(payload),
    });
}

export async function fetchVirtualUsersClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-users");
    const payload = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("users/virtual", {
        method: "POST",
        body: JSON.stringify(payload),
    });
}

export async function fetchAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-all-admins");
    const payload = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("admins/all", {
        method: "POST",
        body: JSON.stringify(payload),
    });
}

export async function fetchVirtualAdminsClient(): Promise<Response> {
    const csrfToken = await fetchCsrfClient("get-virtual-admins");
    const payload = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("admins/virtual", {
        method: "POST",
        body: JSON.stringify(payload),
    });
}
