/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { RefObject, useEffect } from "react";

export function useModalClose(modalRef: RefObject<HTMLDivElement | null>, visible: boolean, onClose: () => void): void {
    useEffect(() => {
        if (!visible) return;

        const handleClickOutside = (e: MouseEvent): void => {
            if (modalRef.current && !modalRef.current.contains(e.target as Node)) {
                onClose();
            }
        };

        const handleKeyDown = (e: KeyboardEvent): void => {
            if (e.key === "Escape") {
                onClose();
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        document.addEventListener("keydown", handleKeyDown);

        return (): void => {
            document.removeEventListener("mousedown", handleClickOutside);
            document.removeEventListener("keydown", handleKeyDown);
        };
    }, [visible, onClose, modalRef]);
}
