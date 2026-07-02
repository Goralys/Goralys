import "@/app/src/lib/bootstrap";
import { ToastProvider } from "@/app/src/ui/toast/toast-provider";
import { ConfirmProvider } from "@/app/src/ui/modals/confirm/confirm-provider";
import { DraftModalProvider } from "@/app/src/ui/modals/drafts/draft-modal-provider";
import { ImportTopicsModalProvider } from "@/app/src/ui/modals/import-topics/import-topics-modal-provider";
import { PasswordModalProvider } from "@/app/src/ui/modals/password/password-modal-provider";
import React, { ReactElement } from "react";
import { EmailModalProvider } from "@/app/src/ui/modals/email/email-modal-provider";

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
