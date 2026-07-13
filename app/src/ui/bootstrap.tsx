"use client";

import { configGoralysCore, CookiesAdapter, cookiesGet, USERNAME_KEY } from "@goralys/core";
import Cookies from "universal-cookie";

export default function Bootstrap(): null {
    configGoralysCore({
        client: { apiDomain: process.env.NEXT_PUBLIC_API_DOMAIN ?? "", privateToken: process.env.NEXT_PUBLIC_API_TOKEN ?? "" },
        storage: {
            getItem: (key) => localStorage.getItem(key),
            getSize: () => localStorage.length,
            keyAt: (idx) => localStorage.key(idx) ?? undefined,
            setItem: (key, value) => localStorage.setItem(key, value),
            removeItem: (key) => localStorage.removeItem(key),
        },
        cookies: ((): CookiesAdapter => {
            const cookies = new Cookies();
            return {
                onChange: (callback) => {
                    cookies.addChangeListener(callback);
                    return () => cookies.removeChangeListener(callback);
                },
                getCookies: (key) => cookies.get(key),
                getAll: () => cookies.getAll(),
                setCookies: (key, value, path = "/", maxAge = 1.5 * 60) => cookies.set(key, value, { path, maxAge }),
                removeCookies: (key, path = "/") => cookies.remove(key, { path }),
            };
        })(),
        auth: () => !!cookiesGet(USERNAME_KEY),
    });

    return null;
}
