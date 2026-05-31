/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { HTTP_METHOD } from "next/dist/server/web/http";

export function buildArray<T>(...items: (T | false | null | undefined)[]): T[] {
    return items.filter((item): item is T => Boolean(item));
}

export type HttpMethod = HTTP_METHOD | "BREW";

export const reasonsConfig = {
    "password-forgot": { label: "Mot de passe oublié" },
    "subject-error": { label: "Question envoyée/validée/rejetée par erreur" },
    "personal-info-error": { label: "Informations personnelles erronées (ex: Nom/Prénom)" },
    other: { label: "Autre (précisez)" },
} as const;

export type SupportReasons = keyof typeof reasonsConfig;

interface PHPDateTime {
    date: string;
    timezone_type: number;
    timezone: string;
}

export type SupportTicket = {
    id: number;
    reason: SupportReasons;
    opener: string;
    openerToken: string;
    message: string;
    createdAt: PHPDateTime;
};

export const parsePhpDateTime = (phpDate: PHPDateTime): string => {
    return new Date(phpDate.date).toLocaleString("fr-FR", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

export type UserRole = {
    role: "admin" | "teacher" | "student" | "none";
};

export const USER_ROLES = ["admin", "teacher", "student", "none"] as const satisfies ReadonlyArray<UserRole["role"]>;

export type SubjectStatus = "not_submitted" | "submitted" | "rejected" | "approved";

export const SubjectStatusConfig: Record<
    SubjectStatus,
    {
        label: string;
        helper: Record<UserRole["role"], string>;
    }
> = {
    not_submitted: {
        label: "Non envoyée",
        helper: {
            student: "Cette question n'a pas encore été envoyée.",
            teacher: "L'élève n'a pas encore envoyé cette question.",
            admin: "Cette question n'a pas encore été envoyée.",
            none: "Role invalide",
        },
    },
    submitted: {
        label: "Envoyée",
        helper: {
            student: "Cette question est en attente de validation, vous ne pouvez plus la modifiée.",
            teacher: "Cette question est en attente de validation.",
            admin: "Cette question est en attente de validation.",
            none: "Role invalide",
        },
    },
    rejected: {
        label: "Rejetée",
        helper: {
            student: "Cette question n'a pas été validée par le professeur, vous devez en envoyer une nouvelle.",
            teacher: "Vous n'avez pas validé cette question, l'élève doit en envoyer une nouvelle.",
            admin: "Cette question n'a pas été validée par le professeur, l'élève doit en envoyer une nouvelle.",
            none: "Role invalide",
        },
    },
    approved: {
        label: "Approuvée",
        helper: {
            student: "Cette question a été validée par le professeur, vous ne pouvez plus la modifier.",
            teacher: "Vous avez validée cette question, elle ne peut plus être modifiée.",
            admin: "Cette question a été validée par le professeur, elle ne peut plus être modifiée.",
            none: "Role invalide",
        },
    },
};

// Helpers
export const getStatusLabel = (status: SubjectStatus): string => SubjectStatusConfig[status].label ?? "";

export const getStatusHelper = (status: SubjectStatus, role: UserRole["role"]): string => SubjectStatusConfig[status].helper[role] ?? "";

export type Subject = {
    comment: string;
    hasDraft: boolean;
    lastRejected?: string;
    status: SubjectStatus;
    student: string;
    studentToken: string;
    subject: string;
    teacher: string;
    teacherToken: string;
    topic: string;
    interdisciplinary: boolean;
};

export type Toast = {
    type: "error" | "warning" | "info" | "success";
    title: string;
    message: string;
    expires?: number;
};

export type AuthEvent = "unauthenticated" | "expired";

export type UserEvent = "login" | "logout" | "register";

export type ConfirmOptions = {
    title: string;
    message: string;
};

export type DraftModalResult = { type: "withDraft"; file: File | null } | { type: "withoutDraft" } | { type: "closed" };

export type UserData = {
    username: string;
    full_name: string;
    role: UserRole["role"];
    public_id: string;
    email: string | null | undefined;
};

export type User = {
    username: string;
    publicId: string;
    fullName: string;
    role: UserRole["role"];
};

export type CookieValue = string | boolean | number | null | undefined;

export const searchFields = {
    all: "Tout",
    student: "Élèves",
    teacher: "Professeur",
    topic: "Matière",
} as const;

export type SubjectsSearchField = keyof typeof searchFields;
