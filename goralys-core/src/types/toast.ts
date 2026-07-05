/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export type Toast = {
    type: "error" | "warning" | "info" | "success";
    title: string;
    message: string;
    expires?: number;
};

export type ToastFn = (toast: Toast, duration?: number) => void;
