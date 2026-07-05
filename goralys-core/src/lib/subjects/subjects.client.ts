/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

"use client";

import { UserRole } from "@/types/user";
import { buildApiUrl, fetchCsrfClient, goralysFetchClient } from "@/lib/fetch/fetch.client";

// Subjects fetches
async function fetchStudentSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-student-subjects");

    if (!csrfToken) return null;

    return await goralysFetchClient("GET", buildApiUrl("subjects/student", { "csrf-token": csrfToken }));
}

async function fetchTeacherSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-teacher-subjects");

    if (!csrfToken) return null;

    return await goralysFetchClient("GET", buildApiUrl("subjects/teacher", { "csrf-token": csrfToken }));
}

async function fetchAdminSubjectsClient(): Promise<Response | null> {
    const csrfToken = await fetchCsrfClient("get-admin-subjects");

    if (!csrfToken) return null;

    return await goralysFetchClient("GET", buildApiUrl("subjects/admin", { "csrf-token": csrfToken }));
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
