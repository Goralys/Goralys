/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

export type UserRole = {
    role: "admin" | "teacher" | "student" | "none";
};

export const USER_ROLES = ["admin", "teacher", "student", "none"] as const satisfies ReadonlyArray<UserRole["role"]>;

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

export type AuthEvent = "unauthenticated" | "expired";

export type UserEvent = "login" | "logout" | "register";

export type ConfirmOptions = {
    title: string;
    message: string;
};
