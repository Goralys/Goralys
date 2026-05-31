/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import React, { RefCallback, useCallback } from "react";

/**
 * A hook that provides a callback ref to auto-resize a textarea based on its content.
 * It also handles ref forwarding (both RefObject and RefCallback).
 *
 * @param ref The external ref to forward to.
 * @returns A RefCallback to be passed to the textarea's ref prop.
 */
export function useAutoResize(ref: React.Ref<HTMLTextAreaElement> | undefined): RefCallback<HTMLTextAreaElement> {
    return useCallback(
        (el: HTMLTextAreaElement | null) => {
            // Handle ref forwarding
            if (typeof ref === "function") {
                ref(el);
            } else if (ref && "current" in ref) {
                Object.assign(ref, { current: el });
            }

            if (!el) return;

            const resize = (): void => {
                el.style.height = "auto";
                el.style.height = `${el.scrollHeight}px`;
            };

            resize(); // Size correctly on mount
            el.addEventListener("input", resize);
            return () => el.removeEventListener("input", resize); // React 19 callback-ref cleanup
        },
        [ref],
    );
}
