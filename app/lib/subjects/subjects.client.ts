/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

"use client";

import { UserRole } from "@/app/lib/types";
import { fetchCsrfClient, goralysFetchClient } from "@/app/lib/fetch/fetch.client";

// Subjects fetches

async function fetchStudentSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-student-subjects");

    if (!csrfToken) return null;

    const data = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("subjects/student", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });
}

async function fetchTeacherSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-teacher-subjects");

    if (!csrfToken) return null;

    const data = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("subjects/teacher", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });
}

async function fetchAdminSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-admin-subjects");

    if (!csrfToken) return null;

    const data = {
        "csrf-token": csrfToken,
    };

    return await goralysFetchClient("subjects/admin", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });
}

export async function fetchSubjectsForRoleClient(role: UserRole): Promise<Response | null> {
    switch (role.role) {
        case "student":
            return await fetchStudentSubjectsClient();
        case "teacher":
            return await fetchTeacherSubjectsClient();
        case "admin":
            return await fetchAdminSubjectsClient();
        default:
            return null;
    }
}
