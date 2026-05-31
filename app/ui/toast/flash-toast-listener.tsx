"use client";

import { useEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import { useToast } from "@/app/ui/toast/toast-provider";
import { goralysFetchClient } from "@/app/lib/fetch/fetch.client";
import { Toast } from "@/app/lib/types";

export default function FlashToastListener(): null {
    const { showToast } = useToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);
    const pathname = usePathname();

    useEffect(() => {
        let cancelled = false;

        const showCachedToast = (): void => {
            const raw = localStorage.getItem("flash_toast");
            if (!raw) return;

            localStorage.removeItem("flash_toast");

            try {
                const parsed: Toast = JSON.parse(raw);
                if (!parsed.expires) return;

                const remaining = parsed.expires - Date.now();
                if (remaining <= 0) return;

                showToastRef.current(parsed, remaining);
            } catch {}
        };

        const run = async (): Promise<void> => {
            try {
                const res = await goralysFetchClient("GET", "toast/flash", undefined, {
                    cache: "no-store",
                });

                const data = await res.json();

                if (cancelled) return;

                if (data?.toast) {
                    // Server returned a toast — clear cache to avoid double-showing
                    localStorage.removeItem("flash_toast");
                    showToastRef.current({
                        type: data.toast.toastType,
                        title: data.toast.toastTitle,
                        message: data.toast.toastMessage,
                    });
                } else {
                    showCachedToast();
                }
            } catch {
                if (!cancelled) showCachedToast();
            }
        };

        void run();

        return (): void => {
            cancelled = true;
        };
    }, [pathname]);

    return null;
}
