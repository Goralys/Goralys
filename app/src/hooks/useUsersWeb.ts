/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useAdmins, User, useUsers, useVirtualAdmins, useVirtualUsers } from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";

type UserCollectionResult = {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
};

export function useUsersWeb(): UserCollectionResult {
    const { showToast } = useToast();
    return useUsers(showToast);
}

export function useVirtualUsersWeb(): UserCollectionResult {
    const { showToast } = useToast();
    return useVirtualUsers(showToast);
}

export function useAdminsWeb(): UserCollectionResult {
    const { showToast } = useToast();
    return useAdmins(showToast);
}

export function useVirtualAdminsWeb(): UserCollectionResult {
    const { showToast } = useToast();
    return useVirtualAdmins(showToast);
}
