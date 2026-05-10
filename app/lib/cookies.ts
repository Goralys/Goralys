/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Cookies from "universal-cookie";
import { CookieValue } from "@/app/lib/types";

export function setCookiesExpire(duration: number): void {
    localStorage.setItem("goralys-cookies-expire", String(Date.now() + duration * 1000));
}

export function setCookie(cookie: Cookies, key: string, value: CookieValue, maxAge: number): void {
    cookie.set(key, value, {
        path: "/",
        maxAge: maxAge, // Expires in 1.5 hours
        httpOnly: false,
    });
    setCookiesExpire(maxAge);
}
