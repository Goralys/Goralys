/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { PHPDateTime } from "@/types/utils";

export const reasonsConfig = {
    "password-forgot": { label: "Mot de passe oublié" },
    "subject-error": { label: "Question envoyée/validée/rejetée par erreur" },
    "personal-info-error": { label: "Informations personnelles erronées (ex: Nom/Prénom)" },
    other: { label: "Autre (précisez)" },
} as const;

export type SupportReasons = keyof typeof reasonsConfig;

export type SupportTicket = {
    id: number;
    reason: SupportReasons;
    opener: string;
    openerToken: string;
    message: string;
    createdAt: PHPDateTime;
};
