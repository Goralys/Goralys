<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Doc\PDF;

use Dompdf\Dompdf;
use Dompdf\Options;
use Goralys\Core\Subjects\Config\SubjectsExportConfig;
use Goralys\Platform\Doc\PDF\Data\PdfSourceDTO;
use Goralys\Platform\Doc\PDF\Interfaces\PdfExporterInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * Wrapper around the base {@see Dompdf} implementation used to export HTML documents to PDF.
 */
final class DomPdfExporter implements PdfExporterInterface
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
     * Exports an HTML template to a PDF file.
     * @param PdfSourceDTO $pdf The PDF to export.
     * @param string $path The path to export the PDF to.
     * @param string $basePath The root path for assets used during PDF export.
     * @return void
     */
    public function export(PdfSourceDTO $pdf, string $path, string $basePath): void
    {
        $finalSource = str_replace(
            '</head>',
            "<style>\n$pdf->CSS\n</style>\n</head>",
            $pdf->HTML,
        );

        // --- Dompdf options ---
        $options = new Options();
        $options->set('isRemoteEnabled', true);     // allow images
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Marianne');   // matches @font-face name
        $options->set('chroot', $basePath);

        $dompdf = new Dompdf($options);

        // Important: base path for images/fonts
        $dompdf->setBasePath($basePath);

        // Load HTML
        $dompdf->loadHtml($finalSource, 'UTF-8');

        // Paper size
        $dompdf->setPaper('A4');

        // Render PDF
        $dompdf->render();

        // Save to file
        file_put_contents($path, $dompdf->output());
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
