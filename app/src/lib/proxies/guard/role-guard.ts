/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { NextRequest, NextResponse } from "next/server";
import { UserRole } from "@goralys/core";

interface RoleGuardOptions {
    allowedRoles: UserRole["role"][];
    onSuccess?: (role: UserRole["role"], request: NextRequest) => NextResponse;
}

export async function roleGuard(request: NextRequest, { allowedRoles, onSuccess }: RoleGuardOptions): Promise<NextResponse> {
    if (!request.cookies.has("GORALYSSESSID")) {
        return redirectTo(request, "/user/login?reason=unauthenticated");
    }

    const apiUrl = process.env.NEXT_PUBLIC_API_DOMAIN;
    if (!apiUrl) {
        console.error("NEXT_PUBLIC_API_DOMAIN is not set");
        return redirectTo(request, "/user/login?reason=server_error");
    }

    let res: Response;
    try {
        res = await fetch(`${apiUrl}/user/role`, {
            method: "GET",
            headers: {
                cookie: request.headers.get("cookie") ?? "",
                "User-Agent": request.headers.get("user-agent") ?? "",
                "X-Forwarded-Origin": request.headers.get("origin") ?? request.nextUrl.origin,
                "Cache-Control": "no-cache, no-store, must-revalidate",
                Pragma: "no-cache",
                Expires: "0",
            },
            cache: "no-store",
        });
    } catch (err) {
        console.error("Error calling role API:", err);
        return redirectTo(request, "/user/login?reason=server_error");
    }

    if (res.status === 401) {
        return redirectTo(request, "/user/login?reason=unauthenticated", {
            noStore: true,
        });
    }
    if (!res.ok) {
        console.error("Role API returned non-ok status:", res.status);
        return redirectTo(request, "/user/login?reason=unauthenticated");
    }

    let role: UserRole["role"];
    try {
        const data = await res.json();
        role = data?.role;
    } catch (err) {
        console.error("Failed to parse JSON from role API:", err);
        return redirectTo(request, "/user/login?reason=unauthenticated");
    }

    if (!role) {
        console.error("Role API returned no role field");
        return redirectTo(request, "/user/login?reason=unauthenticated");
    }

    if (!allowedRoles.includes(role)) {
        return redirectTo(request, "/user/login?reason=unauthorized");
    }

    return onSuccess?.(role, request) ?? NextResponse.next();
}

function redirectTo(request: NextRequest, path: string, options?: { noStore?: boolean }): NextResponse {
    const response = NextResponse.redirect(new URL(path, request.url));
    if (options?.noStore) {
        response.headers.set("Cache-Control", "no-store, max-age=0");
    }
    return response;
}
