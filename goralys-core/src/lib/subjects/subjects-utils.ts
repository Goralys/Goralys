/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

/**
 * List of school subjects with their short and long names.
 * Each entry is a tuple where the first element is a short name/alias
 * and the second element is the full official name.
 * Some subjects have multiple entries; the first will always be the one picked when displaying on mobile,
 * and must be formattable by {@link formatAlias}.
 */
const SUBJECTS: [string, string][] = [
    // Arts plastiques
    ["arts", "arts plastiques"],
    ["arpla", "arts plastiques"],
    ["apla", "arts plastiques"],
    ["arts-plastiques", "arts plastiques"],

    // Cinéma - audiovisuel
    ["cine.av", "cinéma audiovisuel"],
    ["ciav", "cinéma audiovisuel"],
    ["cinema", "cinéma audiovisuel"],
    ["cav", "cinéma audiovisuel"],

    // Arts du cirque
    ["a.cirque", "arts du cirque"],
    ["cirque", "arts du cirque"],

    // Danse
    ["danse", "danse"],

    // Histoire des arts
    ["hist.arts", "histoire des arts"],
    ["histoire-arts", "histoire des arts"],
    ["hda", "histoire des arts"],

    // Musique
    ["mus", "musique"],
    ["musique", "musique"],

    // Théâtre
    ["thea", "théâtre"],
    ["théâtre", "théâtre"],
    ["theatre", "théâtre"],

    // Histoire-géographie, géopolitique et sciences politiques
    ["HGGSP", "histoire géographie géopolitique et sciences politiques"],
    ["hggsp", "histoire géographie géopolitique et sciences politiques"],

    // Humanités, littérature et philosophie
    ["HLP", "humanité littérature et philosophie"],
    ["hlp", "humanité littérature et philosophie"],
    ["hlphi", "humanité littérature et philosophie"],

    // Langues, littératures et cultures étrangères et régionales
    ["LLCER", "langue littérature et culture étrangère"],
    ["llce", "langue littérature et culture étrangère"],
    ["llcer", "langue littérature et culture étrangère"],

    // Littérature et langues et cultures de l'Antiquité
    ["LLCA", "littérature et langues et cultures de l'antiquité"],
    ["llca", "littérature et langues et cultures de l'antiquité"],
    ["lca", "littérature et langues et cultures de l'antiquité"],

    // Mathématiques
    ["maths", "mathématiques"],
    ["math", "mathématiques"],

    // Numérique et sciences informatiques
    ["num.sci.inf", "numérique et sciences informatiques"],
    ["nsi", "numérique et sciences informatiques"],
    ["nsinf", "numérique et sciences informatiques"],
    ["n.s.i", "numérique et sciences informatiques"],

    // Physique-chimie
    ["PCH", "physique-chimie"],
    ["phch", "physique-chimie"],
    ["pc", "physique-chimie"],

    // Sciences de la vie et de la Terre
    ["SVT", "sciences et vie de la terre"],
    ["svt", "sciences et vie de la terre"],

    // Sciences économiques et sociales
    ["SES", "sciences économiques et sociales"],
    ["ses", "sciences économiques et sociales"],

    // Sciences de l'ingénieur
    ["sci.ing", "sciences de l'ingénieur"],
    ["si", "sciences de l'ingénieur"],
    ["scing", "sciences de l'ingénieur"],

    // Éducation physique, pratiques et culture sportives
    ["EPS", "éducation physique pratiques et culture sportives"],
    ["sport", "éducation physique pratiques et culture sportives"],
    ["eppcs", "éducation physique pratiques et culture sportives"],
];

/**
 * Formats a raw subject abbreviation (alias) for display.
 * - If the abbreviation is already all-uppercase (an acronym like "HGGSP", "NSI"), it is returned as-is.
 * - Otherwise, it is split on periods, each part is capitalized, and rejoined with ". " (e.g. "sci.ing" -> "Sci. Ing.").
 * - A single-part abbreviation with no period is just capitalized (e.g. "maths" -> "Maths").
 * @param a The abbreviation to format.
 * @return string The formatted abbreviation.
 */
function formatAlias(a: string): string {
    if (a.toUpperCase() === a) return a;

    const hasPoints = a.includes(".");
    if (!hasPoints) return capitalize(a);

    let result: string = "";
    const clean = a.toLowerCase().replace(" ", "");
    clean.split(".").forEach((part) => (result += capitalize(part) + ". "));
    return result.trim();
}

const capitalize = (s: string): string => s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();

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

/**
 * Returns the full name of a subject based on its short name or an alias.
 * It searches for the first subject in {@link SUBJECTS} whose short name starts with the given string.
 *
 * @param long The long name tp search for.
 * @returns The subject short name, alias, or prefix if a match is found; otherwise, returns the input string itself.
 */
export function getShortFromLong(long: string): string {
    const search = long.toLowerCase().trim();
    const match = SUBJECTS.find(([, value]) => value.startsWith(search));
    return match ? formatAlias(match[0]) : search;
}
