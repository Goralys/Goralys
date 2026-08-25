/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { emitAuthEvent } from "@/lib/auth/auth-event";
import { emitNavigationEvent } from "./navigation-event";
import { GoralysActionHandler } from "./goralys-action-handler";
import type { GoralysFetchOptions, HttpMethod } from "@/types/http";
import { getGoralysClientConfig } from "./config";
import { ToastFn } from "@/types/toast";

const actionHandler = new GoralysActionHandler();

export async function goralysFetchClient(
    method: HttpMethod,
    input: string | URL | Request,
    payload?: Record<string, string | number | boolean | null> | FormData,
    requestOptions?: GoralysFetchOptions,
): Promise<Response> {
    if (requestOptions?.body)
        console.error("[Fetch] Found body inside request options. This is not allowed, consider using the payload parameter instead");

    const { apiDomain, getSchoolToken, isNative } = getGoralysClientConfig();
    const { headers: extraHeaders, ...restOptions } = requestOptions ?? {};

    const res = await fetch(`${apiDomain}/${input}`, {
        credentials: "include",
        method: method === "BREW" ? "POST" : method,
        headers: {
            ...(method === "BREW" ? { "X-HTTP-Method-Override": method } : {}),
            "X-High-School-Token": getSchoolToken(),
            "X-Goralys-Client": isNative ? "mobile" : "desktop",
            ...extraHeaders,
        },
        body: payload ? (payload instanceof FormData ? payload : JSON.stringify(payload)) : undefined,
        ...restOptions,
    });

    const clone = res.clone();
    const contentType = clone.headers.get("Content-Type");
    if (!(contentType && contentType.toLowerCase().trim().includes("application/json"))) return res;

    const data = await clone.json();

    if (data?.redirect && !requestOptions?.suppressRedirect) {
        emitNavigationEvent({ type: "redirect", url: data.redirect });
    }

    if (res.status === 401) {
        try {
            emitAuthEvent(data?.authEvent ?? "unauthenticated");
        } catch {
            emitAuthEvent("unauthenticated");
        }
    }

    if (res.status === 418) {
        const teapotData = await res.clone().json();
        emitNavigationEvent({
            type: "teapot",
            toastType: teapotData.toastType,
            toastTitle: teapotData.toastTitle,
            toastMessage: teapotData.toastMessage,
        });
    }

    await actionHandler.handle(res);
    return res;
}

export async function fetchCsrfClient(formId: string): Promise<string | null> {
    const { apiDomain, getSchoolToken, isNative } = getGoralysClientConfig();
    const res = await fetch(`${apiDomain}/csrf`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-High-School-Token": getSchoolToken(),
            "X-Goralys-Client": isNative ? "mobile" : "desktop",
        },
        credentials: "include",
        body: JSON.stringify({ form: formId }),
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json["csrf-token"];
}

function buildQueryString(params: Record<string, string | null>): string {
    return Object.entries(params)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value!)}`)
        .join("&");
}

export function buildApiUrl(endpoint: string, params: Record<string, string | null>, domain: boolean = false): string {
    const { apiDomain } = getGoralysClientConfig();
    const queryString = buildQueryString(params);
    return `${domain ? apiDomain + "/" : ""}${endpoint}${queryString ? `?${queryString}` : ""}`;
}

export async function handleToastRequest(r: Response, showToast: ToastFn, redirect: boolean = true, duration?: number): Promise<boolean> {
    const res = r.clone();
    const data = await res.json();

    if (data?.toast) {
        showToast({ type: data.toastType, title: data.toastTitle, message: data.toastMessage }, duration);
        if (data?.redirect && redirect) {
            emitNavigationEvent({ type: "redirect", url: data.redirect });
        }
        return true;
    }
    return false;
}
