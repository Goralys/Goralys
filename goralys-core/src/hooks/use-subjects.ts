/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { useCallback } from "react";
import { UserRole } from "@/types/user";
import { fetchSubjectsForRoleClient } from "@/lib/subjects/subjects.client";
import { SUBJECT_CACHES, SUBJECT_SYNCS } from "@/lib/config";
import { Subject } from "@/types/subjects";
import { useSyncedResource } from "@/hooks/use-synced-ressource";

export function useSubjects(role: UserRole["role"]): {
    subjects: Subject[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const fetcher = useCallback(async () => fetchSubjectsForRoleClient({ role }), [role]);
    const parse = useCallback((data: unknown): Subject[] | null => (Array.isArray(data) ? data : null), []);

    const {
        data: subjects,
        refetch,
        syncKey,
    } = useSyncedResource({
        name: "useSubjects",
        cacheKey: SUBJECT_CACHES[role],
        syncKey: SUBJECT_SYNCS[role],
        fetcher,
        parse,
    });

    return { subjects, refetch, syncKey };
}
