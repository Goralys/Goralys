/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { UserRole } from "@/types/user";

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
            student: "Cette question est en attente de validation, vous ne pouvez plus la modifier.",
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

export const searchFields = {
    all: "Tout",
    student: "Élèves",
    teacher: "Professeur",
    topic: "Matière",
} as const;

export type SubjectsSearchField = keyof typeof searchFields;

export type DraftModalResult = { type: "withDraft"; file: File | null } | { type: "withoutDraft" } | { type: "closed" };

export const getStatusLabel = (status: SubjectStatus): string => SubjectStatusConfig[status].label ?? "";

export const getStatusHelper = (status: SubjectStatus, role: UserRole["role"]): string => SubjectStatusConfig[status].helper[role] ?? "";
