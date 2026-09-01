/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { PHPDateTime } from "@/types/utils";

export type UserRole = {
    role: "admin" | "teacher" | "student" | "none";
};

export const USER_ROLES = ["admin", "teacher", "student", "none"] as const satisfies ReadonlyArray<UserRole["role"]>;

export type UserData = {
    username: string;
    fullName: string;
    role: UserRole["role"];
    publicId: string;
    email: string | null | undefined;
};

export type UserProfile = {
    id: number;
    username: string;
    pubId: string;
    fullName: string;
    role: UserRole["role"];
    email: string;
    createdAt: PHPDateTime;
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

export type AuthTokenContext = "admin-panel" | "profile";

export type AuthToken = {
    username: string;
    name: string;
    expires: PHPDateTime;
    created: PHPDateTime;
};
