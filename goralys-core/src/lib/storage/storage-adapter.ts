/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export interface StorageAdapter {
    getItem(key: string): string | null;
    getSize(): number;
    keyAt(ids: number): string | undefined;
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

export const storageGet = (key: string): string | null => getAdapter().getItem(key);

export const storageSet = (key: string, value: string): void => getAdapter().setItem(key, value);

export const storageSize = (): number => getAdapter().getSize();

export const storageKeyAt = (idx: number): string | undefined => getAdapter().keyAt(idx);

export const storageRemove = (key: string): void => getAdapter().removeItem(key);
