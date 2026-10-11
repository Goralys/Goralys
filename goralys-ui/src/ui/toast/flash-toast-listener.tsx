"use client";

import { useEffect, useLayoutEffect, useRef } from "react";
import { useToast } from "#lib/ui/toast/toast-provider";
import { buildApiUrl, FLASH_TOAST_KEY, goralysFetchClient, storageGet, storageRemove, Toast } from "@goralys/core";
import { useGoralysUI } from "#lib/hooks/goralys-ui-provider";

export function FlashToastListener(): null {
    const { showToast } = useToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);
    const pathname = useGoralysUI().usePathname();

    const showCachedToast = (destroyCache: boolean = true): void => {
        const raw = storageGet(FLASH_TOAST_KEY);
        if (!raw) return;

        if (destroyCache) storageRemove(FLASH_TOAST_KEY);

        try {
            const parsed: Toast = JSON.parse(raw);
            if (!parsed.expires) return;

            const remaining = parsed.expires - Date.now();
            if (remaining <= 0) return;

            showToastRef.current(parsed, false, remaining);
        } catch {}
    };

    useLayoutEffect(() => showCachedToast(false), [pathname]);

    useEffect(() => {
        let cancelled = false;

        const run = async (): Promise<void> => {
            try {
                const res = await goralysFetchClient("GET", buildApiUrl("toast/flash", {}), undefined, {
                    cache: "no-store",
                });

                const data = await res.json();

                if (data?.toast) {
                    // Server returned a toast — clear cache to avoid double-showing
                    storageRemove(FLASH_TOAST_KEY);
                    showToastRef.current({
                        type: data.toast.toastType,
                        title: data.toast.toastTitle,
                        message: data.toast.toastMessage,
                    });
                }
                if (cancelled) return;

                showCachedToast();
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
