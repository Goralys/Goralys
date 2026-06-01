<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Doc\PDF\Interfaces;

use Goralys\Platform\Doc\PDF\Data\PdfSourceDTO;

/**
 * Contract for PDF exporters.
 */
interface PdfExporterInterface
{
    /**
     * Ensure all required directories are created (and creates them if they aren't).
     * @return void
     */
    public function prepare(): void;

    /**
     * Deletes all exported files (PDFs and zips) from the exports directory.
     * @return void
     */
    public function clean(): void;

    /**
     * Renders a PDF document and writes it to disk.
     * @param PdfSourceDTO $pdf The source data describing the PDF to generate.
     * @param string $path The destination file path where the PDF will be written.
     * @param string $basePath The base path used to resolve relative assets (images, fonts, etc.).
     */
    public function export(PdfSourceDTO $pdf, string $path, string $basePath): void;
}
