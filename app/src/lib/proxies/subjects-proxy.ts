/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { NextRequest, NextResponse } from "next/server";
import { roleGuard } from "@/app/src/lib/proxies/guard/role-guard";

export async function SubjectsProxy(request: NextRequest): Promise<NextResponse> {
    const { pathname } = request.nextUrl;

    return roleGuard(request, {
        onSuccess: (role) => {
            if (pathname === "/subject") {
                return NextResponse.redirect(new URL(`/subject/${role}`, request.url));
            } else {
                return NextResponse.next();
            }
        },
        allowedRoles: ["admin", "teacher", "student"],
    });
}
