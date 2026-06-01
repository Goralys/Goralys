"use client";
"use no memo";

import { createContext, useContext, useState, ReactNode, useCallback, useMemo, ReactElement } from "react";
import ImportTopicsModalElement from "@/app/src/ui/modals/import-topics/import-topics-modal-element";
import { createPortal } from "react-dom";

export type ImportTopicsModalContext = {
    showImportTopicsModal: () => Promise<File | string | null>;
};

const ImportTopicsModalContext = createContext<ImportTopicsModalContext | null>(null);

export function ImportTopicsModalProvider({ children }: { children: ReactNode }): ReactElement {
    const [fileChosen, setChosenFile] = useState<{
        resolve: (value: File | string | null) => void;
    } | null>(null);
    const [visible, setVisible] = useState(false);

    const showImportTopicsModal = useCallback((): Promise<File | string | null> => {
        return new Promise((resolve: (value: File | string | null) => void) => {
            setChosenFile({ resolve });
            setVisible(false);
            requestAnimationFrame(() => setVisible(true));
        });
    }, []);

    function handleImportTopics(file: File | null): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve(file);
            setChosenFile(null);
        }, 500);
    }

    function handleCancel(): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve(null);
            setChosenFile(null);
        }, 500);
    }

    function handleClose(): void {
        setVisible(false);
        setTimeout(() => {
            fileChosen?.resolve("modalClosed");
            setChosenFile(null);
        }, 500);
    }

    // eslint-disable-next-line react-hooks/preserve-manual-memoization
    const value = useMemo(() => ({ showImportTopicsModal }), [showImportTopicsModal]);

    return (
        <ImportTopicsModalContext.Provider value={value}>
            {children}

            {fileChosen &&
                typeof document !== "undefined" &&
                createPortal(
                    <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm">
                        <ImportTopicsModalElement
                            visible={visible}
                            onImportTopicsAction={handleImportTopics}
                            onCancelAction={handleCancel}
                            onCloseModalAction={handleClose}
                        />
                    </div>,
                    document.getElementById("import-topics-modal-root")!,
                )}
        </ImportTopicsModalContext.Provider>
    );
}

export function useImportTopicsModal(): ImportTopicsModalContext {
    const context = useContext(ImportTopicsModalContext);
    if (!context) {
        throw new Error("useImportTopicsModal must be used within a ConfirmProvider");
    }
    return context;
}
