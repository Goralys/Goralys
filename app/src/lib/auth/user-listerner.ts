/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useEffect } from "react";
import { emptyUserCacheClient, onUserEvent } from "@goralys/core";

export function UserListener(): null {
    useEffect(() => {
        return onUserEvent((event) => {
            if (event === "logout") {
                (async (): Promise<void> => {
                    try {
                        emptyUserCacheClient();
                    } catch (err) {
                        console.error("[UserListener] Failed to clear user cache:", err);
                    } finally {
                        setTimeout(() => {
                            window.location.href = "/user/login";
                        }, 0);
                    }
                })();
            }
        });
    }, []);

    return null;
}
