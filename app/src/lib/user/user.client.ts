/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import {
    cookiesGetAll,
    cookiesRemove,
    cookiesSet,
    EMAIL_KEY,
    FULL_NAME_KEY,
    goralysFetchClient,
    PERSISTANT_COOKIES,
    PERSISTANT_LOCALS,
    PUB_ID_KEY,
    ROLE_KEY,
    storageRemove,
    UserData,
    USERNAME_KEY,
} from "@goralys/core";

export async function cacheUserDataClientWeb(): Promise<void> {
    const res = await goralysFetchClient("GET", "user/profile");

    if (!res.ok) {
        return;
    }

    const data = (await res.json())["data"] as UserData;

    cookiesSet(USERNAME_KEY, data.username, "/", 1.5 * 60 * 60);
    cookiesSet(FULL_NAME_KEY, data.full_name, "/", 1.5 * 60 * 60);
    cookiesSet(ROLE_KEY, data.role, "/", 1.5 * 60 * 60);
    cookiesSet(PUB_ID_KEY, data.public_id, "/", 1.5 * 60 * 60);
    if (data?.email) cookiesSet(EMAIL_KEY, data.email, "/", 1.5 * 60 * 60);
    else cookiesRemove(EMAIL_KEY);
}

export function emptyUserCacheClientWeb(): void {
    // Invalidate old local cache
    for (let i: number = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i)!; // 'i' must be a valid index (see loop above)
        if (!PERSISTANT_LOCALS.includes(key)) storageRemove(key);
    }

    Object.keys(cookiesGetAll()).forEach((name) => {
        if (PERSISTANT_COOKIES.includes(name.trim())) return; // do not delete persistant cookies
        if (name.startsWith("__next")) return; // do not delete Next.js cookies
        cookiesRemove(name);
    });
}
