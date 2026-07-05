/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export interface StorageAdapter {
    getItem(key: string): string | null;
    setItem(key: string, value: string): void;
    removeItem(key: string): void;
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

export function storageGet(key: string): string | null {
    return getAdapter().getItem(key);
}

export function storageSet(key: string, value: string): void {
    getAdapter().setItem(key, value);
}

export function storageRemove(key: string): void {
    getAdapter().removeItem(key);
}
