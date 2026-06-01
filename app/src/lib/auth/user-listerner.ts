/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useEffect } from "react";
import { onUserEvent } from "@/app/src/lib/auth/user-event";
import { emptyUserCacheClient } from "@/app/src/lib/user/user.client";

export function UserListener(): null {
    useEffect(() => {
        return onUserEvent((event) => {
            if (event === "logout") {
                emptyUserCacheClient();
                setTimeout(() => {
                    window.location.href = "/user/login";
                }, 0);
            }
        });
    }, []);

    return null;
}
