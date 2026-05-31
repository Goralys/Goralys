/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useEffect, useRef } from "react";
import { useRouter } from "next/navigation";
import { useToast } from "@/app/ui/toast/toast-provider";
import { onAuthEvent } from "@/app/src/lib/auth/auth-event";
import { emitUserEvent } from "@/app/src/lib/auth/user-event";

export function AuthListener(): null {
    const router = useRouter();
    const toast = useToast();
    const toastRef = useRef(toast);
    useEffect(() => {
        toastRef.current = toast;
    }, [toast]);
    const routerRef = useRef(router);
    useEffect(() => {
        routerRef.current = router;
    }, [router]);

    useEffect(() => {
        return onAuthEvent((event) => {
            if (event === "expired") {
                toastRef.current.showToast({
                    type: "warning",
                    title: "Session",
                    message: "Votre session a expiré, vous avez été déconnecté.",
                });

                setTimeout(() => {
                    routerRef.current.replace("/user/login");
                }, 0);
            } else if (event === "unauthenticated") {
                toastRef.current.showToast({
                    type: "info",
                    title: "Connexion",
                    message: "Veuillez vous connecter pour accéder à votre espace",
                });

                setTimeout(() => {
                    routerRef.current.replace("/user/login");
                }, 0);
            }
            emitUserEvent("logout");
        });
    }, []);

    return null;
}
