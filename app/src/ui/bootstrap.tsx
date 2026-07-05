"use client";

import { configGoralysCore, CookiesAdapter, cookiesGet, USERNAME_KEY } from "@goralys/core";
import { cacheUserDataClientWeb, emptyUserCacheClientWeb } from "@/app/src/lib/user/user.client";
import Cookies from "universal-cookie";

export default function Bootstrap(): null {
    configGoralysCore({
        client: { apiDomain: process.env.NEXT_PUBLIC_API_DOMAIN ?? "" },
        cache: { cache: cacheUserDataClientWeb, empty: emptyUserCacheClientWeb },
        storage: {
            getItem: (key) => localStorage.getItem(key),
            setItem: (key, value) => localStorage.setItem(key, value),
            removeItem: (key) => localStorage.removeItem(key),
        },
        cookies: ((): CookiesAdapter => {
            const cookies = new Cookies();
            return {
                getCookies: (key) => cookies.get(key),
                getAll: () => cookies.getAll(),
                setCookies: (key, value, path = "/", maxAge = 1.5 * 60) => cookies.set(key, value, { path, maxAge }),
                removeCookies: (key) => cookies.remove(key),
                onChange: (callback) => {
                    cookies.addChangeListener(callback);
                    return () => cookies.removeChangeListener(callback);
                },
            };
        })(),
        auth: () => !!cookiesGet(USERNAME_KEY),
    });

    return null;
}
