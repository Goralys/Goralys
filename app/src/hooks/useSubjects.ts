/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Subject, UserRole } from "@/app/lib/types";
import { useToast } from "@/app/ui/toast/toast-provider";
import Cookies from "universal-cookie";
import { fetchSubjectsForRoleClient } from "@/app/lib/subjects/subjects.client";
import { handleToastRequest } from "@/app/lib/fetch/fetch.client";

export function useSubjects(role: UserRole["role"]): {
    subjects: Subject[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const [subjects, setSubjects] = useState<Subject[] | null>(null);
    const { showToast } = useToast();
    const showToastRef = useRef(showToast);
    useEffect(() => {
        showToastRef.current = showToast;
    }, [showToast]);

    const cookiesRef = useRef<Cookies>(new Cookies());

    const inFlightRef = useRef<Promise<void> | null>(null);

    const fetchSubjects = useCallback(async () => {
        const cookies = cookiesRef.current;
        const cacheKey = `subjects-cache-${role}`;
        const syncKey = `subjects-synced-${role}`;

        console.log("[useSubjects] fetchSubjects called", { role });

        if (!cookies.get("username")) {
            console.log("[useSubjects] no username cookie, aborting");
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
            const syncValue = cookies.get(syncKey);

            if (syncValue == "1") {
                const raw = localStorage.getItem(cacheKey);
                if (raw === null || raw === undefined) {
                    cookies.set(syncKey, "0", { path: "/" });
                    localStorage.removeItem(cacheKey);
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

            cookies.set(syncKey, "1", { path: "/" });
            localStorage.setItem(cacheKey, JSON.stringify(data));
            console.log("[useSubjects] set syncKey and cached to localStorage");
            console.log("[useSubjects] localStorage after set:", localStorage.getItem(cacheKey)?.slice(0, 100));

            const result = Array.isArray(data) ? data : null;
            console.log("[useSubjects] setting subjects:", result ? `array(${result.length})` : result);
            setSubjects((prev) => {
                if (JSON.stringify(prev) === JSON.stringify(result)) return prev;
                return result;
            });
        } finally {
            inFlightRef.current = null;
            resolve!();
        }
    }, [role]);

    useEffect(() => {
        const cookies = new Cookies();
        const onChange = (): void => {
            if (inFlightRef.current) return;
            const syncKey = `subjects-synced-${role}`;
            if (cookies.get(syncKey) != "1") {
                void fetchSubjects();
            }
        };

        cookies.addChangeListener(onChange);
        return (): void => cookies.removeChangeListener(onChange);
    }, [fetchSubjects, role]);

    useEffect(() => {
        void fetchSubjects();
    }, [fetchSubjects]);

    return useMemo(
        () => ({
            subjects,
            refetch: fetchSubjects,
            syncKey: `subjects-synced-${role}`,
        }),
        [subjects, fetchSubjects, role],
    );
}
