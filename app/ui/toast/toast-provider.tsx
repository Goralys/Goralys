"use client";

import { createContext, ReactElement, ReactNode, useCallback, useContext, useMemo, useState } from "react";
import ToastElement from "@/app/ui/toast/toast-element";
import { Toast } from "@/app/lib/types";
import { createPortal } from "react-dom";

export type ToastContext = {
    showToast: (toast: Toast, duration?: number) => void;
    cacheToast: (toast: Toast, duration?: number) => void;
};

const ToastContext = createContext<ToastContext | null>(null);

export function ToastProvider({ children }: { children: ReactNode }): ReactElement {
    const [toast, setToast] = useState<Toast | null>(null);
    const [visible, setVisible] = useState(false);

    const cacheToast = useCallback((toastInput: Toast, duration: number = 5500) => {
        if (!toastInput.expires) toastInput.expires = Date.now() + duration;
        localStorage.setItem("flash_toast", JSON.stringify(toastInput));
    }, []);

    const showToast = useCallback(
        (toastInput: Toast, duration: number = 5500) => {
            if (!toastInput.expires) toastInput.expires = Date.now() + duration;
            setToast(toastInput);
            setVisible(false);

            requestAnimationFrame(() => setVisible(true));

            setTimeout(() => setVisible(false), duration - 500);
            setTimeout(() => setToast(null), duration);
            cacheToast(toastInput, duration);
        },
        [cacheToast],
    );

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
