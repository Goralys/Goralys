/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { UserRole } from "@/types/user";
import { fetchSubjectsForRoleClient } from "@/lib/subjects/subjects.client";
import { handleToastRequest } from "@/lib/fetch/fetch.client";
import { SUBJECT_CACHES, SUBJECT_SYNCS } from "@/lib/config";
import { isAuthenticated } from "@/lib/auth/check-auth";
import { storageGet, storageRemove, storageSet } from "@/lib/storage/storage-adapter";
import { ToastFn } from "@/types/toast";
import { Subject } from "@/types/subjects";
import { cookiesGet, cookiesOnChange, cookiesSet } from "@/lib/storage/cookies-adapter";

export function useSubjects(
    role: UserRole["role"],
    showToast: ToastFn,
): {
    subjects: Subject[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [subjects, setSubjects] = useState<Subject[] | null>(null);
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchSubjects = useCallback(async () => {
        const cacheKey = SUBJECT_CACHES[role];
        const syncKey = SUBJECT_SYNCS[role];

        console.log("[useSubjects] fetchSubjects called", { role });

        if (!isAuthenticated()) {
            console.log("[useSubjects] not authenticated, aborting");
            return;
        }

        if (inFlightRef.current) {
            console.log("[useSubjects] in-flight, waiting");
            return inFlightRef.current;
        }

        let resolve: () => void;
        inFlightRef.current = new Promise<void>((r) => {
            resolve = r;
        });

        try {
            const syncValue = cookiesGet(syncKey);

            if (syncValue == "1") {
                const raw = storageGet(cacheKey);
                if (raw === null || raw === undefined) {
                    cookiesSet(syncKey, "0");
                    storageRemove(cacheKey);
                    await fetchSubjects();
                    return;
                }
                const cached = JSON.parse(raw ?? "null");
                setSubjects((prev) => {
                    if (JSON.stringify(prev) === JSON.stringify(cached)) return prev;
                    return cached;
                });
                return;
            }

            const res = await fetchSubjectsForRoleClient({ role });
            if (res) await handleToastRequest(res, showToastRef.current, false);
            const data = await res?.json();

            cookiesSet(syncKey, "1");
            storageSet(cacheKey, JSON.stringify(data));
            console.log("[useSubjects] set syncKey and cached to localStorage");

            const result = Array.isArray(data) ? data : null;
            setSubjects((prev) => {
                if (JSON.stringify(prev) === JSON.stringify(result)) return prev;
                return result;
            });
        } finally {
            inFlightRef.current = null;
            resolve!();
        }
    }, [role, showToastRef]);

    useEffect(() => {
        const onChange = (): void => {
            if (inFlightRef.current) return;
            if (cookiesGet(SUBJECT_SYNCS[role]) != "1") {
                void fetchSubjects();
            }
        };

        return cookiesOnChange(onChange);
    }, [fetchSubjects, role]);

    useEffect(() => {
        void fetchSubjects();
    }, [fetchSubjects]);

    return useMemo(
        () => ({
            subjects,
            refetch: fetchSubjects,
            syncKey: SUBJECT_SYNCS[role],
        }),
        [subjects, fetchSubjects, role],
    );
}
