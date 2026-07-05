import { CookieValue } from "@/types/utils";

export interface CookiesAdapter {
    onChange(callback: () => void): () => void;
    getCookies(key: string): CookieValue;
    getAll(): Record<string, CookieValue>;
    setCookies(key: string, value: CookieValue, path: string, maxAge: number): void;
    removeCookies(key: string, path: string): void;
}

let adapter: CookiesAdapter | null = null;

export function configureCookies(customAdapter: CookiesAdapter): void {
    adapter = customAdapter;
}

function getAdapter(): CookiesAdapter {
    if (!adapter) {
        throw new Error("Cookies not configured. Call configureCookies() first.");
    }
    return adapter;
}

export const cookiesGet = (key: string): CookieValue => getAdapter().getCookies(key);

export const cookiesGetAll = (): Record<string, CookieValue> => getAdapter().getAll();

export const cookiesSet = (key: string, value: string, path: string = "/", maxAge: number = 1.5 * 60): void =>
    getAdapter().setCookies(key, value, path, maxAge);

export const cookiesRemove = (key: string, path: string = "/"): void => getAdapter().removeCookies(key, path);

export const cookiesOnChange = (callback: () => void): (() => void) => getAdapter().onChange(callback);

// export function setCookiesExpire(duration: number): void {
//     storageSet("goralys-cookies-expire", String(Date.now() + duration * 1000)); // ignore promise
// }
