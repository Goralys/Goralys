import { AuditAction, buildApiUrl, fetchCsrfClient, goralysFetchClient, useSyncedResource } from "@goralys/core";
import { useCallback } from "react";

export function useAuditLogs(target: string): { actions: AuditAction[] | null; refetch: () => Promise<undefined | void>; syncKey: string } {
    const fetcher = useCallback(async () => {
        const csrfToken = await fetchCsrfClient("get-admin-audit-long");

        if (!csrfToken) return null;

        return await goralysFetchClient("GET", buildApiUrl("admin/audit/long", { target, "csrf-token": csrfToken }));
    }, [target]);
    const parse = useCallback((data: unknown): AuditAction[] | null => (Array.isArray(data) ? data : null), []);

    const {
        data: actions,
        refetch: refetch,
        syncKey,
    } = useSyncedResource({
        name: "useAdminPanelActions",
        cacheKey: "actions-admin-panel-cache-" + target,
        syncKey: "actions-admin-panel-synced-" + target,
        fetcher,
        parse,
    });

    return { actions, refetch, syncKey };
}
