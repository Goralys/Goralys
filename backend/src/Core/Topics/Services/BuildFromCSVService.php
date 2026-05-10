<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Services;

use Goralys\Core\Topics\Config\TopicsImportConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\Utils\String\Data\StringCase;
use Goralys\Shared\Utils\UtilitiesManager;
use RuntimeException;
use SplFileObject;

/**
 * Service responsible for building topic-related data (groups and students) from CSV files.
 */
final class BuildFromCSVService
{
    private UtilitiesManager $utils;
    private TopicsImportConfig $config;

    /**
     * @param UtilitiesManager $utils
     * @param TopicsImportConfig $config
     */
    public function __construct(UtilitiesManager $utils, TopicsImportConfig $config)
    {
        $this->utils = $utils;
        $this->config = $config;
    }

    /**
     * Ensures the provided path is a valid CSV file and returns a SplFileObject.
     * @param string $path The full path to the CSV file.
     * @return SplFileObject
     * @throws GoralysRuntimeException If the file is not a valid CSV or cannot be opened.
     */
    private function ensureCSV(string $path): SplFileObject
    {
        if (!is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'csv') {
            throw new GoralysRuntimeException(
                "The provided file ($path) is not a valid CSV file.",
            );
        }

        try {
            $file = new SplFileObject($path, 'r');

            $file->setFlags(
                SplFileObject::READ_CSV
                | SplFileObject::SKIP_EMPTY
                | SplFileObject::DROP_NEW_LINE,
            );

            $file->setCsvControl(escape: '');

            return $file;
        } catch (RuntimeException $e) {
            throw new GoralysRuntimeException(
                "Could not open CSV file ($path).",
                previous: $e,
            );
        }
    }

    /**
     * Parses a CSV file to build a mapping of group codes to teacher usernames.
     * @param string $from The full path to the groups CSV file.
     * @return array<string, list<string>> A mapping of group code => array of teacher usernames.
     * @throws GoralysRuntimeException If the CSV format is invalid.
     */
    public function buildGroups(string $from): array
    {
        $file = $this->ensureCSV($from);

        $result = [];

        foreach ($file as $i => $row) {
            if ($row === null || $row === false || $row === [null]) {
                continue;
            }

            if (count($row) !== 2) {
                throw new GoralysRuntimeException(
                    "CSV format error at line " . ($i + 1) . ": expected 2 columns.",
                );
            }

            [$groupId, $teachersRaw] = $row;

            $groupId = trim($groupId);
            $groupId = ltrim($groupId, "\xEF\xBB\xBF"); // Remove UTF-8 BOM
            $teachersRaw = trim($teachersRaw);

            $teachers = array_map('trim', explode($this->config::TEACHERS_SEPARATOR, $teachersRaw));
            $teachers = array_values(array_filter($teachers, fn(string $t) => $t !== ''));

            $result[$groupId] = $teachers;
        }

        return $result;
    }

    /**
     * Parses a CSV file to extract student names from a specific column.
     * It attempts to automatically detect the student column based on common headers.
     * @param string $from The full path to the student CSV file.
     * @return string[] A unique list of student names.
     * @throws GoralysRuntimeException If the CSV file cannot be opened.
     */
    public function buildStudents(string $from): array
    {
        $file = $this->ensureCSV($from);
        $students = [];
        $firstRow = null;
        foreach ($file as $row) {
            if ($row === null || $row === false || $row === [null]) {
                continue;
            }
            $firstRow = $row;
            if (isset($firstRow[0])) {
                $firstRow[0] = ltrim($firstRow[0], "\xEF\xBB\xBF"); // Remove UTF-8 BOM
            }
            break;
        }

        if ($firstRow === null) {
            return [];
        }

        $normalized = array_map(
            fn($v) => $this->utils->string->sanitize((string) $v, StringCase::LOWER),
            $firstRow,
        );

        $accepted = ['élève', 'student', 'étudiant', 'nom'];
        $accepted = array_map(
            fn($v) => $this->utils->string->sanitize((string) $v, StringCase::LOWER),
            $accepted,
        );

        $studentCol = null;
        foreach ($normalized as $idx => $name) {
            if (in_array($name, $accepted, true)) {
                $studentCol = $idx;
                break;
            }
        }

        $hasHeader = ($studentCol !== null);

        if (!$hasHeader) {
            $studentCol = 0;

            $s = isset($firstRow[$studentCol]) ? trim((string) $firstRow[$studentCol]) : '';
            if ($s !== '' && mb_check_encoding($s, 'UTF-8') && !str_contains($s, "\u{FFFD}")) {
                $students[] = $s;
            }
        }

        $file->rewind();

        $headerSkipped = false;

        foreach ($file as $row) {
            if ($row === null || $row === false || $row === [null]) {
                continue;
            }

            if ($hasHeader && !$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $s = isset($row[$studentCol]) ? trim((string) $row[$studentCol]) : '';
            if ($s === '') {
                continue;
            }

            $students[] = $s;
        }

        return array_values(array_unique($students));
    }
}
