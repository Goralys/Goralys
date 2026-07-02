/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export interface StorageAdapter {
    getItem(key: string): string | null | Promise<string | null>;
    setItem(key: string, value: string): void | Promise<void>;
    removeItem(key: string): void | Promise<void>;
}

let adapter: StorageAdapter | null = null;

export function configureStorage(customAdapter: StorageAdapter): void {
    adapter = customAdapter;
}

function getAdapter(): StorageAdapter {
    if (!adapter) {
        throw new Error("Storage not configured. Call configureStorage() first.");
    }
    return adapter;
}

export async function storageGet(key: string): Promise<string | null> {
    return getAdapter().getItem(key);
}

export async function storageSet(key: string, value: string): Promise<void> {
    await getAdapter().setItem(key, value);
}

export async function storageRemove(key: string): Promise<void> {
    await getAdapter().removeItem(key);
}
