<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Doc\PDF\Interfaces;

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
     * Renders a batch of PDF documents and writes them to disk.
     * @param array $batch The source data describing the PDFs to generate.
     */
    public function export(array $batch): void;
}
