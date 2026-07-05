/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { SupportTicket, useSupportTickets } from "@goralys/core";
import { useToast } from "@/app/src/ui/toast/toast-provider";

export function useSupportTicketsWeb(): {
    supportTickets: SupportTicket[] | null;
    refetch: () => Promise<undefined | void>;
    syncKey: string;
} {
    const { showToast } = useToast();
    return useSupportTickets(showToast);
}
