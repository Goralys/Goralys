// custom hook to allow the admins to get stats on any user's subjects

import { useCallback } from "react";
import { AuditAction, buildApiUrl, fetchCsrfClient, goralysFetchClient, Subject, useSyncedResource } from "@goralys/core";

export function useAdminPanelInfo(target: string): {
    subjects: Subject[] | null;
    actions: AuditAction[] | null;
    refetchS: () => Promise<undefined | void>;
    refetchA: () => Promise<undefined | void>;
} {
    const fetcher = useCallback(
        async (endpoint: string, csrfId: string) => {
            const csrfToken = await fetchCsrfClient(csrfId);

            if (!csrfToken) return null;

            return await goralysFetchClient("GET", buildApiUrl(endpoint, { target, "csrf-token": csrfToken }));
        },
        [target],
    );
    const parse = useCallback(<T>(data: unknown): T[] | null => (Array.isArray(data) ? data : null), []);

    const { data: subjects, refetch: refetchS } = useSyncedResource({
        name: "useAdminPanelSubjects",
        cacheKey: "subjects-admin-panel-cache-" + target,
        syncKey: "subjects-admin-panel-synced-" + target,
        fetcher: async () => await fetcher("subjects/any", "get-user-subjects"),
        parse: parse<Subject>,
    });

    const { data: actions, refetch: refetchA } = useSyncedResource({
        name: "useAdminPanelActions",
        cacheKey: "actions-admin-panel-cache-" + target,
        syncKey: "actions-admin-panel-synced-" + target,
        fetcher: async () => await fetcher("admin/audit/short", "get-admin-audit-short"),
        parse: parse<AuditAction>,
    });

    return { subjects, actions, refetchS, refetchA };
}
