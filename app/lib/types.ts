/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export function buildArray<T>(...items: (T | false | null | undefined)[]): T[] {
    return items.filter((item): item is T => Boolean(item));
}

export type UserRole = {
    role: "admin" | "teacher" | "student" | "none";
};

export const USER_ROLES = ["admin", "teacher", "student", "none"] as const satisfies ReadonlyArray<UserRole["role"]>;

export type SubjectStatus = "not_submitted" | "submitted" | "rejected" | "approved";

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
