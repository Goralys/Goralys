// custom hook to allow the admins to get stats on any user's subjects

import { useCallback } from "react";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient, Subject, useSyncedResource } from "@goralys/core";

export function useAdminPanelSubjects(target: string): {
    subjects: Subject[] | null;
    refetch: () => Promise<undefined | void>;
} {
    const fetcher = useCallback(async () => {
        const csrfToken = await fetchCsrfClient("get-user-subjects");

        if (!csrfToken) return null;

        return await goralysFetchClient("GET", buildApiUrl("subjects/any", { target, "csrf-token": csrfToken }));
    }, [target]);
    const parse = useCallback((data: unknown): Subject[] | null => (Array.isArray(data) ? data : null), []);

    const { data: subjects, refetch } = useSyncedResource({
        name: "useAdminPanelSubjects",
        cacheKey: "subjects-admin-panel-cache-" + target,
        syncKey: "subjects-admin-panel-synced-" + target,
        fetcher,
        parse,
    });

    return { subjects, refetch };
}
