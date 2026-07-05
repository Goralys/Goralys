/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { NextRequest, NextResponse } from "next/server";
import { isFoolsDay } from "@goralys/core";

export async function FoolsProxy(request: NextRequest): Promise<NextResponse> {
    const { pathname } = request.nextUrl;

    const isFoolsRoute = ["/coffee", "/tea"].includes(pathname);

    if (isFoolsRoute && !isFoolsDay()) {
        return NextResponse.rewrite(new URL("/404", request.url), { status: 404 });
    }

    return NextResponse.next();
}
