/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

import { PHPDateTime } from "@/types/utils";

export function buildArray<T>(...items: (T | false | null | undefined)[]): T[] {
    return items.filter((item): item is T => Boolean(item));
}

export const fromPhpDate = (phpDate: PHPDateTime): Date => new Date(phpDate.date);

export const parsePhpDateTime = (phpDate: PHPDateTime): string => {
    return fromPhpDate(phpDate).toLocaleString("fr-FR", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

export function separateNames(fullName: string): { firstName: string; lastName: string } {
    const parts = fullName.split(" ");
    const firstNameParts: string[] = [];
    const lastNameParts: string[] = [];
    let current: "first" | "last" = "first";

    parts.forEach((p) => {
        if (p.toUpperCase() === p) current = "last";

        switch (current) {
            case "first":
                firstNameParts.push(p);
                break;
            case "last":
                lastNameParts.push(p);
                break;
        }
    });

    return { firstName: firstNameParts.join(" "), lastName: lastNameParts.join(" ") };
}
