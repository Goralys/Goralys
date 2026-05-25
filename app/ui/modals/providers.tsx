import { ToastProvider } from "@/app/ui/toast/toast-provider";
import { ConfirmProvider } from "@/app/ui/modals/confirm/confirm-provider";
import { DraftModalProvider } from "@/app/ui/modals/drafts/draft-modal-provider";
import { ImportTopicsModalProvider } from "@/app/ui/modals/import-topics/import-topics-modal-provider";
import { PasswordModalProvider } from "@/app/ui/modals/password/password-modal-provider";
import React, { ReactElement } from "react";
import { EmailModalProvider } from "@/app/ui/modals/email/password-modal-provider";

export function Providers({ children }: { children: React.ReactNode }): ReactElement {
    return (
        <>
            <div id="toast-root"></div>
            <div id="confirm-root"></div>
            <div id="draft-modal-root"></div>
            <div id="import-topics-modal-root"></div>
            <div id="password-modal-root"></div>
            <div id="email-modal-root"></div>

            <ToastProvider>
                <ConfirmProvider>
                    <DraftModalProvider>
                        <ImportTopicsModalProvider>
                            <EmailModalProvider>
                                <PasswordModalProvider>{children}</PasswordModalProvider>
                            </EmailModalProvider>
                        </ImportTopicsModalProvider>
                    </DraftModalProvider>
                </ConfirmProvider>
            </ToastProvider>
        </>
    );
}
