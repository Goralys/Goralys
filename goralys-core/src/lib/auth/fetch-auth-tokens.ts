import { cookiesGet } from "@/lib/storage/cookies-adapter";
import { USERNAME_KEY } from "@/lib/config";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/lib/fetch/fetch.client";
import { AuthTokenContext } from "@/types/user";

export async function fetchTokensProfile(): Promise<Response | null> {
    const username = cookiesGet(USERNAME_KEY);
    if (!username) {
        console.error("[fetchTokensProfile] No username cookies, aborting");
        return null;
    }
    const csrf = await fetchCsrfClient("get-auth-tokens");
    return await goralysFetchClient("GET", buildApiUrl("user/tokens", { "csrf-token": csrf }));
}

export async function fetchTokensAdminPanel(target: string): Promise<Response | null> {
    const username = cookiesGet(USERNAME_KEY);
    if (!username) {
        console.error("[fetchTokensProfile] No username cookies, aborting");
        return null;
    }
    const csrf = await fetchCsrfClient("get-auth-tokens");
    return await goralysFetchClient("GET", buildApiUrl("user/tokens/any", { pubId: target, "csrf-token": csrf }));
}

export async function fetchAuthTokensForContext(ctx: AuthTokenContext, target: string | null = null): Promise<Response | null> {
    switch (ctx) {
        case "profile":
            return await fetchTokensProfile();
        case "admin-panel":
            return await fetchTokensAdminPanel(target!);
    }
}
