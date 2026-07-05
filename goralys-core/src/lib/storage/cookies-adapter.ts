import { CookieValue } from "@/types/utils";
import { storageSet } from "@/lib/storage/storage-adapter";

export interface CookiesAdapter {
    onChange(callback: () => void): () => void;
    getCookies(key: string): CookieValue;
    getAll(): Record<string, CookieValue>;
    setCookies(key: string, value: CookieValue, path: string, maxAge: number): void;
    removeCookies(key: string): void;
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

export function cookiesGet(key: string): CookieValue {
    return getAdapter().getCookies(key);
}

export function cookiesGetAll(): Record<string, CookieValue> {
    return getAdapter().getAll();
}

export function cookiesSet(key: string, value: string, path: string = "/", maxAge: number = 1.5 * 60): void {
    getAdapter().setCookies(key, value, path, maxAge);
}

export function cookiesRemove(key: string): void {
    getAdapter().removeCookies(key);
}

export function cookiesOnChange(callback: () => void): () => void {
    return getAdapter().onChange(callback);
}

export function setCookiesExpire(duration: number): void {
    storageSet("goralys-cookies-expire", String(Date.now() + duration * 1000)); // ignore promise
}
