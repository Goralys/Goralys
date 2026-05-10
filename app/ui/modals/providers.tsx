import { ToastProvider } from "@/app/ui/toast/toast-provider";
import { ConfirmProvider } from "@/app/ui/modals/confirm/confirm-provider";
import { DraftModalProvider } from "@/app/ui/modals/drafts/draft-modal-provider";
import { ImportTopicsModalProvider } from "@/app/ui/modals/import-topics/import-topics-modal-provider";
import { PasswordModalProvider } from "@/app/ui/modals/password/password-modal-provider";
import React, { ReactElement } from "react";

export function Providers({ children }: { children: React.ReactNode }): ReactElement {
    return (
        <ToastProvider>
            <ConfirmProvider>
                <DraftModalProvider>
                    <ImportTopicsModalProvider>
                        <PasswordModalProvider>{children}</PasswordModalProvider>
                    </ImportTopicsModalProvider>
                </DraftModalProvider>
            </ConfirmProvider>
        </ToastProvider>
    );
}
