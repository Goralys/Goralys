/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * List of school subjects with their short and long names.
 * Each entry is a tuple where the first element is a short name/alias
 * and the second element is the full official name.
 */
const SUBJECTS: [string, string][] = [
    ["apla", "arts plastiques"],
    ["arts-plastiques", "arts plastiques"],
    ["arts", "arts plastiques"],
    ["ciav", "cinéma audiovisuel"],
    ["cinema", "cinéma audiovisuel"],
    ["cav", "cinéma audiovisuel"],
    ["hggsp", "histoire géographie géopolitique et sciences politiques"],
    ["hlphi", "humanité littérature et philosophie"],
    ["hlp", "humanité littérature et philosophie"],
    ["llce", "langue littérature et culture étrangère"],
    ["llcer", "langue littérature et culture étrangère"],
    ["maths", "mathématiques"],
    ["math", "mathématiques"],
    ["nsinf", "numérique et sciences informatiques"],
    ["nsi", "numérique et sciences informatiques"],
    ["phch", "physique-chimie"],
    ["pc", "physique-chimie"],
    ["ses", "sciences économiques et sociales"],
    ["svt", "sciences et vie de la terre"],
];

/**
 * Returns the full name of a subject based on its short name or an alias.
 * It searches for the first subject in {@link SUBJECTS} whose short name starts with the given string.
 *
 * @param short The short name, alias, or prefix to search for.
 * @returns The full subject name if a match is found; otherwise, returns the input string itself.
 */
export function getLongFromShort(short: string): string {
    const search = short.toLowerCase().trim();
    const match = SUBJECTS.find(([key]) => key.startsWith(search));
    return match ? match[1] : search;
}
