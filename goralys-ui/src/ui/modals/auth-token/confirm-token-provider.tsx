"use client";

import { createContext, ReactElement, ReactNode, useCallback, useContext, useMemo, useState } from "react";
import { createPortal } from "react-dom";
import { ConfirmTokenElement } from "#lib/ui/modals/auth-token/confirm-token-element";

type Result = {
    confirm: boolean;
    askAgain: boolean;
};

export type ConfirmTokenContext = {
    showConfirmToken: (username: string) => Promise<Result>;
};

const ConfirmTokenContext = createContext<ConfirmTokenContext | null>(null);

export function ConfirmTokenProvider({ children }: { children: ReactNode }): ReactElement {
    const [confirmState, setConfirmState] = useState<{
        username: string;
        resolve: (value: Result) => void;
    } | null>(null);
    const [visible, setVisible] = useState(false);

    const showConfirmToken = useCallback((username: string): Promise<Result> => {
        return new Promise((resolve) => {
            setConfirmState({ username, resolve });
            setVisible(false);
            requestAnimationFrame(() => setVisible(true));
        });
    }, []);

    function handleConfirm(askAgain: boolean): void {
        setVisible(false);
        setTimeout(() => {
            confirmState?.resolve({ confirm: true, askAgain });
            setConfirmState(null);
        }, 500);
    }

    function handleCancel(): void {
        setVisible(false);
        setTimeout(() => {
            confirmState?.resolve({ confirm: false, askAgain: false });
            setConfirmState(null);
        }, 500);
    }

    const value = useMemo(() => ({ showConfirmToken }), [showConfirmToken]);

    return (
        <ConfirmTokenContext.Provider value={value}>
            {children}

            {confirmState &&
                typeof document !== "undefined" &&
                createPortal(
                    <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm">
                        <ConfirmTokenElement
                            savedUsername={confirmState.username}
                            visible={visible}
                            onConfirmAction={handleConfirm}
                            onCancelAction={handleCancel}
                        />
                    </div>,
                    document.getElementById("confirm-token-root")!,
                )}
        </ConfirmTokenContext.Provider>
    );
}

export function useConfirmToken(): ConfirmTokenContext {
    const context = useContext(ConfirmTokenContext);
    if (!context) {
        throw new Error("useConfirmToken must be used within a ConfirmTokenProvider");
    }
    return context;
}
