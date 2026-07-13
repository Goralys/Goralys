"use client";

import { useEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { buildApiUrl, FLASH_TOAST_KEY, goralysFetchClient, storageGet, storageRemove, Toast } from "@goralys/core";

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
            const raw = storageGet(FLASH_TOAST_KEY);
            if (!raw) return;

            storageRemove(FLASH_TOAST_KEY);

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
                const res = await goralysFetchClient("GET", buildApiUrl("toast/flash", {}), undefined, {
                    cache: "no-store",
                });

                const data = await res.json();

                if (cancelled) return;

                if (data?.toast) {
                    // Server returned a toast — clear cache to avoid double-showing
                    storageRemove(FLASH_TOAST_KEY);
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
