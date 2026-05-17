"use client";
"use no memo";

import { createContext, useContext, useState, ReactNode, useCallback, useMemo, ReactElement } from "react";
import PasswordModalElement from "@/app/ui/modals/password/password-modal-element";
import { createPortal } from "react-dom";

export type PasswordModalContext = {
    showPasswordModal: (confirm?: string) => Promise<string | null>;
};

const PasswordModalContext = createContext<PasswordModalContext | null>(null);

export function PasswordModalProvider({ children }: { children: ReactNode }): ReactElement {
    const [state, setState] = useState<{
        resolve: (value: string | null) => void;
    } | null>(null);
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState<string>("");
    const DEFAULT_PASSWORD_CONFIRM = "cette action";

    const showPasswordModal = useCallback((confirm: string = DEFAULT_PASSWORD_CONFIRM): Promise<string | null> => {
        setMessage("Veuillez entrer votre mot de passe pour confirmer " + confirm.replace(".", " ").trim() + ".");
        return new Promise((resolve) => {
            setState({ resolve });
            setVisible(false);
            requestAnimationFrame(() => setVisible(true));
        });
    }, []);

    function handleConfirm(password: string): void {
        setVisible(false);
        setTimeout(() => {
            state?.resolve(password);
            setState(null);
        }, 500);
    }

    function handleCancel(): void {
        setVisible(false);
        setTimeout(() => {
            state?.resolve(null);
            setState(null);
        }, 500);
    }

    function handleClose(): void {
        setVisible(false);
        setTimeout(() => {
            state?.resolve(null);
            setState(null);
        }, 500);
    }

    const value = useMemo(() => ({ showPasswordModal }), [showPasswordModal]);

    return (
        <PasswordModalContext.Provider value={value}>
            {children}

            {state &&
                typeof document !== "undefined" &&
                createPortal(
                    <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm">
                        <PasswordModalElement
                            message={message}
                            visible={visible}
                            onConfirmAction={handleConfirm}
                            onCancelAction={handleCancel}
                            onCloseModalAction={handleClose}
                        />
                    </div>,
                    document.getElementById("password-modal-root")!,
                )}
        </PasswordModalContext.Provider>
    );
}

export function usePasswordModal(): PasswordModalContext {
    const context = useContext(PasswordModalContext);
    if (!context) {
        throw new Error("usePasswordModal must be used within a PasswordModalProvider");
    }
    return context;
}
