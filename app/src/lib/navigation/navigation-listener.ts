/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useEffect } from "react";
import { onNavigationEvent } from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";

export function NavigationListener(): null {
    const toast = useToast();

    useEffect(() => {
        return onNavigationEvent((event) => {
            if (event.type === "teapot") {
                const params = encodeURIComponent(
                    JSON.stringify({
                        toastType: event.toastType,
                        toastTitle: event.toastTitle,
                        toastMessage: event.toastMessage,
                    }),
                );
                window.location.href = `/errors/teapot?toast=${params}`;
            } else if (event.type === "redirect") {
                window.location.href = event.url;
            }
        });
    }, [toast]);

    return null;
}
