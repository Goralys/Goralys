/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { useAdmins, User, useUsers, useVirtualAdmins, useVirtualUsers } from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { useCrossTabSync } from "./utils";

type UserCollectionResult = {
    users: User[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
};

export function useUsersWeb(): UserCollectionResult {
    const { showToast } = useToast();
    const core = useUsers(showToast);
    useCrossTabSync(core.refetch, core.syncKey);
    return core;
}

export function useVirtualUsersWeb(): UserCollectionResult {
    const { showToast } = useToast();
    const core = useVirtualUsers(showToast);
    useCrossTabSync(core.refetch, core.syncKey);
    return core;
}

export function useAdminsWeb(): UserCollectionResult {
    const { showToast } = useToast();
    const core = useAdmins(showToast);
    useCrossTabSync(core.refetch, core.syncKey);
    return core;
}

export function useVirtualAdminsWeb(): UserCollectionResult {
    const { showToast } = useToast();
    const core = useVirtualAdmins(showToast);
    useCrossTabSync(core.refetch, core.syncKey);
    return core;
}
