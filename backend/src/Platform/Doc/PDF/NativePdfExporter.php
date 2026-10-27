<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Doc\PDF;

use Exception;
use Goralys\Core\Subjects\Config\SubjectsExportConfig;
use Goralys\Platform\Doc\PDF\Interfaces\PdfExporterInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Random\RandomException;

/**
 * Wrapper around the Rust goralys template renderer extension. This extension is located in the backend/native folder
 * and uses the Rust crate ironpress to render pdf.
 */
final class NativePdfExporter implements PdfExporterInterface
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger The injected logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Deletes all exported files (PDFs and zips) from the exports directory.
     * @return void
     * @throws GoralysRuntimeException If the exports directory does not exist or a file cannot be deleted.
     */
    public function clean(): void
    {
        $exportsDir = SubjectsExportConfig::EXPORT_BASE_DIR;

        if (!is_dir($exportsDir)) {
            throw new GoralysRuntimeException("Exports directory not found at: $exportsDir");
        }

        $files = array_merge(glob($exportsDir . "*.pdf"), glob($exportsDir . "*.zip"));

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            if (!unlink($file)) {
                throw new GoralysRuntimeException("Failed to delete export file: $file");
            }
        }
    }

    /**
     * Exports a batch of jobs based on an HTML template to PDF files.
     * @param array $batch The list of PDFs to export.
     * @return void
     * @throws GoralysRuntimeException If the export fails.
     */
    public function export(array $batch): void
    {
        try {
            $suffix = bin2hex(random_bytes(1));
        } catch (RandomException) {
            $suffix = "rand0m";
        }
        $tmpFile = Config::DIRECTORIES::CACHE . "export_batch_" . $suffix;

        // put batch into file
        file_put_contents($tmpFile, json_encode($batch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        try {
            export_jobs_from($tmpFile); // renders and exports the jobs
        } catch (Exception $e) {
            throw new GoralysRuntimeException("Error occurred during job rendering: " . $e->getMessage());
        }

        // remove temporary file
        // unlink($tmpFile);
    }

    /**
     * Ensure all required directories are created (and creates them if they aren't).
     * @return void
     */
    public function prepare(): void
    {
        $exportDir = SubjectsExportConfig::EXPORT_BASE_DIR;
        if (!is_dir($exportDir)) {
            if (!mkdir($exportDir, 0o777)) {
                $this->logger->warning(LoggerInitiator::PLATFORM, "Failed to create dir: " . $exportDir);
            }
        }
        foreach (SubjectsExportConfig::EXPORT_BROKEN_DIRS as $dir) {
            if (!is_dir($exportDir . $dir)) {
                if (!mkdir($exportDir . $dir, 0o777)) {
                    $this->logger->warning(LoggerInitiator::PLATFORM, "Failed to create dir: " . $exportDir . $dir);
                }
            }
        }
    }
}
