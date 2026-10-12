"use client";

import { createContext, ReactElement, ReactNode, useCallback, useContext, useEffect, useMemo, useRef, useState } from "react";
import { ToastElement } from "#lib/ui/toast/toast-element";
import { FLASH_TOAST_KEY, storageSet, Toast } from "@goralys/core";
import { createPortal } from "react-dom";

export type ToastContext = {
    showToast: (toast: Toast, animate?: boolean, duration?: number) => void;
    cacheToast: (toast: Toast, duration?: number) => void;
};

const ToastContext = createContext<ToastContext | null>(null);

export function ToastProvider({ children }: { children: ReactNode }): ReactElement {
    const [toast, setToast] = useState<Toast | null>(null);
    const toastRef = useRef<Toast | null>(toast);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        toastRef.current = toast;
    }, [toast]);

    const cacheToast = useCallback((toastInput: Toast, duration: number = 5500) => {
        if (!toastInput.expires) toastInput.expires = Date.now() + duration;
        storageSet(FLASH_TOAST_KEY, JSON.stringify(toastInput));
    }, []);

    const showToast = useCallback(
        (toastInput: Toast, animate: boolean = true, duration: number = 5500) => {
            cacheToast(toastInput, duration);

            if (!toastInput.expires) toastInput.expires = Date.now() + duration;
            if (toastInput.expires === toastRef.current?.expires) return;

            toastRef.current = toastInput;
            setToast(toastInput);
            setVisible(!animate);

            if (animate) requestAnimationFrame(() => setVisible(true));
        },
        [cacheToast],
    );

    useEffect(() => {
        if (!toast || !toast.expires) return;

        const left = toast.expires - Date.now();
        const hide = setTimeout(() => setVisible(false), left - 500);
        const clear = setTimeout(() => setToast(null), left);

        return (): void => {
            clearTimeout(hide);
            clearTimeout(clear);
        };
    }, [toast]);

    const value = useMemo(() => ({ showToast, cacheToast }), [showToast, cacheToast]);
    return (
        <ToastContext.Provider value={value}>
            {children}

            {toast &&
                typeof document !== "undefined" &&
                createPortal(<ToastElement {...toast} visible={visible} />, document.getElementById("toast-root")!)}
        </ToastContext.Provider>
    );
}

export function useToast(): ToastContext {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error("useToast must be used in a valid context.");
    }
    return context;
}
