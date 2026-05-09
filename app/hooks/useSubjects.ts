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
            console.log("[useSubjects] syncKey:", syncKey, "=", syncValue);

            if (syncValue == "1") {
                const raw = sessionStorage.getItem(cacheKey);
                console.log("[useSubjects] cache hit, raw sessionStorage value:", raw?.slice(0, 100));
                const cached = JSON.parse(raw ?? "null");
                console.log("[useSubjects] parsed cache:", Array.isArray(cached) ? `array(${cached.length})` : cached);
                setSubjects((prev) => {
                    if (JSON.stringify(prev) === JSON.stringify(cached)) return prev;
                    return cached;
                });
                return;
            }

            console.log("[useSubjects] cache miss, fetching from server...");
            const res = await fetchSubjectsForRoleClient({ role });
            const data = await res?.json();
            console.log("[useSubjects] server response:", Array.isArray(data) ? `array(${data.length})` : data);

            if (data?.toast) {
                showToastRef.current({
                    type: data.toastType,
                    title: data.toastTitle,
                    message: data.toastMessage,
                });
            }

            cookies.set(syncKey, "1", { path: "/" });
            sessionStorage.setItem(cacheKey, JSON.stringify(data));
            console.log("[useSubjects] set syncKey and cached to sessionStorage");
            console.log("[useSubjects] sessionStorage after set:", sessionStorage.getItem(cacheKey)?.slice(0, 100));

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
