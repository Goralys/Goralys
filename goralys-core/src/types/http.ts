/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: LGPL-3.0-or-later
 */

/**
 * This filed is a slightly modified version of Next.js own http method declaration.
 */

export declare const HTTP_METHODS: readonly ["GET", "HEAD", "OPTIONS", "POST", "PUT", "DELETE", "PATCH", "BREW"];

export type HttpMethod = (typeof HTTP_METHODS)[number];

/**
 * Checks to see if the passed string is an HTTP method. Note that this is case-sensitive.
 *
 * @param maybeMethod the string that may be an HTTP method.
 * @returns true if the string is an HTTP method.
 */
export declare function isHTTPMethod(maybeMethod: string): maybeMethod is HttpMethod;
