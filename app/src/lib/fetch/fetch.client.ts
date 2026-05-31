/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// Client only fetches helpers.

"use client";

import { emitAuthEvent } from "@/app/src/lib/auth/auth-event";
import { GoralysActionHandler } from "@/app/src/lib/fetch/goralys-action-handler";
import { ToastContext } from "@/app/ui/toast/toast-provider";
import { HttpMethod } from "@/app/src/lib/types";

const apiUrl = process.env.NEXT_PUBLIC_API_DOMAIN;
const actionHandler = new GoralysActionHandler();

/**
 * Custom function to detect session expiration when fetching data.
 * If a 401-response code is detected, the user is redirected to the login page with a toast.
 * Else, the function returns the response of the fetch request.
 * @param method The HTTP method of the request.
 * @param input The url to fetch (relative to the public api domain).
 * @param payload The payload of the request.
 * @param requestOptions The options of the request, they are the same as for a normal fetch call.
 * @return Promise<Response> The result of the request.
 */
export async function goralysFetchClient(
    method: HttpMethod,
    input: string | URL | Request,
    payload?: Record<string, string | number | boolean | null> | FormData,
    requestOptions?: RequestInit,
): Promise<Response> {
    const res = await fetch(`${apiUrl}/${input}`, {
        credentials: "include",
        method: method === "BREW" ? "POST" : method,
        headers: method === "BREW" ? { "X-HTTP-Method-Override": method } : {},
        body: payload ? (payload instanceof FormData ? payload : JSON.stringify(payload)) : undefined,
        ...requestOptions,
    });
    // Ensure JSON before parsing:
    const clone = res.clone();
    const contentType = clone.headers.get("Content-Type");

    if (!(contentType && contentType.toLowerCase().trim().includes("application/json"))) return res;

    const data = await clone.json();
    console.log("[FetchClient](" + input + ") Raw data: ", data);
    // Auth check
    if (res.status === 401) {
        try {
            emitAuthEvent(data?.authEvent ?? "unauthenticated");
        } catch {
            emitAuthEvent("unauthenticated");
        }
    }

    // Tea pot
    if (res.status === 418) {
        const data = await res.clone().json();
        const params = encodeURIComponent(
            JSON.stringify({
                toastType: data.toastType,
                toastTitle: data.toastTitle,
                toastMessage: data.toastMessage,
            }),
        );
        window.location.href = `/errors/teapot?toast=${params}`;
    }

    await actionHandler.handle(res);
    return res;
}

export async function fetchCsrfClient(formId: string): Promise<string | null> {
    const data = {
        form: formId,
    };

    const res = await fetch(`${apiUrl}/csrf`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify(data),
    });

    if (!res.ok) return null;

    const json = await res.json();
    return json["csrf-token"];
}

/**
 * Helper function to build query strings from parameters.
 * @param params Query parameters as key-value pairs.
 * @return string The encoded query string (without leading ?).
 */
function buildQueryString(params: Record<string, string | null>): string {
    return (
        Object.entries(params)
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
            .filter(([_, value]) => value !== null && value !== undefined)
            .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value!)}`)
            .join("&")
    );
}

/**
 * Helper function to build api request url from endpoint and parameters.
 * @param endpoint The endpoint to fetch
 * @param params Query parameters as key-value pairs.
 * @param domain Wether to append the domain at the start of the url.
 * @return string The encoded query string (without leading ?).
 */
export function buildApiUrl(endpoint: string, params: Record<string, string | null>, domain: boolean = false): string {
    const queryString = buildQueryString(params);
    return `${domain ? process.env.NEXT_PUBLIC_API_DOMAIN + "/" : ""}${endpoint}${queryString ? `?${queryString}` : ""}`;
}

export async function handleToastRequest(
    r: Response,
    showToast: ToastContext["showToast"],
    redirect: boolean = true,
    duration?: number,
): Promise<boolean> {
    const res = r.clone();

    const data = await res.json();

    if (data?.toast) {
        showToast(
            {
                type: data.toastType,
                title: data.toastTitle,
                message: data.toastMessage,
            },
            duration,
        );
        if (data?.redirect && redirect) window.location.href = data?.redirect;

        return true;
    }

    return false;
}
