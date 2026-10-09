import {
    buildApiUrl,
    fetchCsrfClient,
    FILTER_OPTIONS_CACHE,
    FILTER_OPTIONS_SYNC,
    goralysFetchClient,
    SubjectsFilterOptions,
    useSyncedResource,
} from "@goralys/core";

export function useFilterOptions(): {
    opt: SubjectsFilterOptions | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const fetcher = async (): Promise<Response> =>
        await goralysFetchClient("GET", buildApiUrl("subjects/filter/opt", { "csrf-token": await fetchCsrfClient("get-filter-options") }));

    const parse = (data: unknown): SubjectsFilterOptions | null =>
        data !== null && typeof data === "object" ? (data as SubjectsFilterOptions) : null;

    const {
        data: opt,
        refetch,
        syncKey,
    } = useSyncedResource({
        name: "useFilterOptions",
        cacheKey: FILTER_OPTIONS_CACHE,
        syncKey: FILTER_OPTIONS_SYNC,
        fetcher,
        parse,
    });

    return {
        opt,
        refetch,
        syncKey,
    };
}
