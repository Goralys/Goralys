"use client";
"use no memo";

import { createContext, useContext, useState, ReactNode, useCallback, useMemo, ReactElement } from "react";
import EmailModalElement from "@/app/ui/modals/email/email-modal-element";
import { createPortal } from "react-dom";

export type EmailModalContext = {
    showEmailModal: () => Promise<string | null>;
};

const EmailModalContext = createContext<EmailModalContext | null>(null);

export function EmailModalProvider({ children }: { children: ReactNode }): ReactElement {
    const [state, setState] = useState<{
        resolve: (value: string | null) => void;
    } | null>(null);
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState<string>("");
    const DEFAULT_EMAIL_CONFIRM = "Veuillez entrer nouvel email pour confirmer.";

    const showEmailModal = useCallback((): Promise<string | null> => {
        setMessage(DEFAULT_EMAIL_CONFIRM);
        return new Promise((resolve) => {
            setState({ resolve });
            setVisible(false);
            requestAnimationFrame(() => setVisible(true));
        });
    }, []);

    function handleConfirm(email: string): void {
        setVisible(false);
        setTimeout(() => {
            state?.resolve(email);
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

    const value = useMemo(() => ({ showEmailModal }), [showEmailModal]);

    return (
        <EmailModalContext.Provider value={value}>
            {children}

            {state &&
                typeof document !== "undefined" &&
                createPortal(
                    <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm">
                        <EmailModalElement
                            message={message}
                            visible={visible}
                            onConfirmAction={handleConfirm}
                            onCancelAction={handleCancel}
                            onCloseModalAction={handleClose}
                        />
                    </div>,
                    document.getElementById("email-modal-root")!,
                )}
        </EmailModalContext.Provider>
    );
}

export function useEmailModal(): EmailModalContext {
    const context = useContext(EmailModalContext);
    if (!context) {
        throw new Error("useEmailModal must be used within a EmailModalProvider");
    }
    return context;
}
