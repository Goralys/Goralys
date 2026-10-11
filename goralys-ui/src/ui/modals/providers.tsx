import { ToastProvider } from "#lib/ui/toast/toast-provider";
import { ConfirmProvider } from "#lib/ui/modals/confirm/confirm-provider";
import { DraftModalProvider } from "#lib/ui/modals/drafts/draft-modal-provider";
import { ImportTopicsModalProvider } from "#lib/ui/modals/import-topics/import-topics-modal-provider";
import { PasswordModalProvider } from "#lib/ui/modals/password/password-modal-provider";
import React, { ReactElement } from "react";
import { EmailModalProvider } from "#lib/ui/modals/email/email-modal-provider";

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
