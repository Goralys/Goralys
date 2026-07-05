/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useToast } from "@/app/src/ui/toast/toast-provider";
import { Subject, UserRole, useSubjects } from "@goralys/core";

export function useSubjectsWeb(role: UserRole["role"]): {
    subjects: Subject[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const { showToast } = useToast();
    return useSubjects(role, showToast);
}
