/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { SupportTicket, useSupportTickets } from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";
import { useCrossTabSync } from "@/app/src/hooks/utils";

export function useSupportTicketsWeb(): {
    supportTickets: SupportTicket[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const { showToast } = useToast();
    const core = useSupportTickets(showToast);
    useCrossTabSync(core.refetch, core.syncKey);
    return core;
}
