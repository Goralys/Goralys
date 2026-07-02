/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Cookies from "universal-cookie";
import { CookieValue, setCookiesExpire } from "@goralys/core";

export function setCookie(cookie: Cookies, key: string, value: CookieValue, maxAge: number = 1.5 * 60 * 60): void {
    cookie.set(key, value, {
        path: "/",
        maxAge: maxAge, // Expires in 1.5 hours
        httpOnly: false,
    });
    setCookiesExpire(maxAge);
}

export function removeCookie(cookie: Cookies, key: string, maxAge: number): void {
    cookie.remove(key, {
        path: "/",
        maxAge: maxAge, // Expires in 1.5 hours
        httpOnly: false,
    });
    setCookiesExpire(maxAge);
}
