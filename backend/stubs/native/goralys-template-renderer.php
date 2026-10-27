<?php

use JetBrains\PhpStorm\FileReference;

/**
 * IDE and static-analysis stubs for the goralys-template-renderer extension (v0.1.0).
 *
 * The real functions are provided by the Rust extension (ext-php-rs).
 * NEVER include, require, or autoload this file: with the extension loaded, PHP would fail with "Cannot redeclare
 * function".
 *
 * Keep in sync with the #[php_function] signatures in native/fiche-renderer/src/lib.rs.
 */

/**
 * Renders every job of a batch JSON file to PDF.
 *
 * @param string $path Path to the batch JSON (template_dir + jobs).
 *
 * @throws Exception If the batch can't be read or a fiche fails to render.
 */
function export_jobs_from(#[FileReference] string $path): void
{
}

/**
 * @param string $name
 *
 * @return string
 */
function hello(string $name): string
{
}
