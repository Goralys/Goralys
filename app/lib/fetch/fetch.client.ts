/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

// Client only fetches helpers.

"use client";

import { emitAuthEvent } from "@/app/lib/auth/auth-event";
import { GoralysActionHandler } from "@/app/lib/fetch/goralys-action-handler";

const apiUrl = process.env.NEXT_PUBLIC_API_DOMAIN;
const actionHandler = new GoralysActionHandler();

/**
 * Custom function to detect session expiration when fetching data.
 * If a 401-response code is detected, the user is redirected to the login page with a toast.
 * Else, the function returns the response of the fetch request.
 * @param input The url to fetch (relative to the public api domain).
 * @param requestOptions The options of the request, they are the same as for a normal fetch call.
 * @return Promise<Response> The result of the request.
 */
export async function goralysFetchClient(input: string | URL | Request, requestOptions?: RequestInit): Promise<Response> {
    const res = await fetch(`${apiUrl}/${input}`, {
        credentials: "include",
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
        const data = await res.json();
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
        "form-id": formId,
    };

    const res = await fetch(`${apiUrl}/csrf/create`, {
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
