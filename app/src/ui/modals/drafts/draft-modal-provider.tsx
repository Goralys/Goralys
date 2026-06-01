"use client";
"use no memo";

import { createContext, useContext, useState, ReactNode, useCallback, useMemo, ReactElement } from "react";
import DraftModalElement from "@/app/src/ui/modals/drafts/draft-modal-element";
import { createPortal } from "react-dom";
import { DraftModalResult } from "@/app/src/lib/types";

export type DraftModalContext = {
    showDraftModal: () => Promise<DraftModalResult>;
};

const DraftModalContext = createContext<DraftModalContext | null>(null);

export function DraftModalProvider({ children }: { children: ReactNode }): ReactElement {
    const [fileChosen, setChosenFile] = useState<{
        resolve: (value: DraftModalResult) => void;
    } | null>(null);
    const [visible, setVisible] = useState(false);

    const showDraftModal = useCallback((): Promise<DraftModalResult> => {
        return new Promise((resolve: (value: DraftModalResult) => void) => {
            setChosenFile({ resolve });
            setVisible(false);
            requestAnimationFrame(() => setVisible(true));
        });
    }, []);

    function handleChooseDraft(file: File | null): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve({ type: "withDraft", file: file });
            setChosenFile(null);
        }, 500);
    }

    function handleCancel(): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve({ type: "withoutDraft" });
            setChosenFile(null);
        }, 500);
    }

    function handleClose(): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve({ type: "closed" });
            setChosenFile(null);
        }, 500);
    }

    // eslint-disable-next-line react-hooks/preserve-manual-memoization
    const value = useMemo(() => ({ showDraftModal }), [showDraftModal]);

    return (
        <DraftModalContext.Provider value={value}>
            {children}

            {fileChosen &&
                typeof document !== "undefined" &&
                createPortal(
                    <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm">
                        <DraftModalElement
                            visible={visible}
                            onChooseDraftAction={handleChooseDraft}
                            onCancelAction={handleCancel}
                            onCloseModalAction={handleClose}
                        />
                    </div>,
                    document.getElementById("draft-modal-root")!,
                )}
        </DraftModalContext.Provider>
    );
}

export function useDraftModal(): DraftModalContext {
    const context = useContext(DraftModalContext);
    if (!context) {
        throw new Error("useDraftModal must be used within a ConfirmProvider");
    }
    return context;
}
