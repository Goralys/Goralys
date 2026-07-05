import { configureGoralysClient, GoralysClientConfig } from "@/lib/fetch/config";
import { configureStorage, StorageAdapter } from "@/lib/storage/storage-adapter";
import { configureCookies, CookiesAdapter } from "@/lib/storage/cookies-adapter";
import { CheckAuthImpl, configureCheckAuth } from "@/lib/auth/check-auth";
import { configureUserCacheCallbacks } from "@/lib/user/user.client";

interface GoralysCoreConfig {
    client: GoralysClientConfig;
    storage: StorageAdapter;
    cookies: CookiesAdapter;
    auth: CheckAuthImpl;
    cache: { cache: () => Promise<void>; empty: () => void };
}

export function configGoralysCore(cfg: GoralysCoreConfig): void {
    configureGoralysClient(cfg.client);
    configureCookies(cfg.cookies);
    configureStorage(cfg.storage);
    configureCheckAuth(cfg.auth);
    configureUserCacheCallbacks(cfg.cache.cache, cfg.cache.empty);
}
