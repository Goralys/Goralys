import { configureCheckAuth, configureGoralysClient, configureStorage, configUserCacheCallbacks, USERNAME_KEY } from "@goralys/core";
import { cacheUserDataClientWeb, emptyUserCacheClientWeb } from "@/app/src/lib/user/user.client";
import Cookies from "universal-cookie";

configureGoralysClient({ apiDomain: process.env.NEXT_PUBLIC_API_DOMAIN ?? "" });

configUserCacheCallbacks(cacheUserDataClientWeb, emptyUserCacheClientWeb);

configureStorage({
    getItem: (key) => localStorage.getItem(key),
    setItem: (key, value) => localStorage.setItem(key, value),
    removeItem: (key) => localStorage.removeItem(key),
});

configureCheckAuth(() => {
    const cookies = new Cookies();
    return !!cookies.get(USERNAME_KEY);
});
