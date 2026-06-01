/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { NextRequest, NextResponse } from "next/server";
import { roleGuard } from "@/app/src/lib/proxies/guard/role-guard";

export async function AdminsProxy(request: NextRequest): Promise<NextResponse> {
    return roleGuard(request, { allowedRoles: ["admin"] });
}
