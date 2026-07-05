<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Services;

use Goralys\App\Topics\Data\StudentDTO;
use Goralys\Core\Topics\Config\TopicsImportConfig;
use Goralys\Shared\Exception\Files\InvalidFileException;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\Lib\String\StringCase;
use Goralys\Shared\User\Data\FullNameDTO;
use RuntimeException;
use SplFileObject;

/**
 * Service responsible for building topic-related data (groups and students) from CSV files.
 */
final class BuildFromCSVService
{
    private TopicsImportConfig $config;

    /**
     * @param TopicsImportConfig $config The injected config for the topics export.
     */
    public function __construct(TopicsImportConfig $config)
    {
        $this->config = $config;
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
                    "CSV format error at line " . ($i + 1) . ": expected 2 columns."
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
     * Parses a CSV file to extract student names from a specific column.
     * It attempts to automatically detect the student column based on common headers.
     * @param string $from The full path to the student CSV file.
     * @return StudentDTO[] A unique list of student names.
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
            fn($v) => Lib::STRING::sanitize((string) $v, StringCase::LOWER),
            $firstRow,
        );
        $studentCol = $this->getColIdx(['élève', 'student', 'étudiant', 'nom'], $normalized);
        $classroomCol = $this->getColIdx(['classe', 'class'], $normalized);

        $headerValid = ($studentCol !== null) && ($classroomCol !== null);

        if (!$headerValid) {
            throw new InvalidFileException(
                "Got invalid CSV file header for topics import: " . print_r($firstRow, true)
            );
        }

        $file->rewind();

        $headerSkipped = false;

        foreach ($file as $row) {
            if ($row === null || $row === false || $row === [null]) {
                continue;
            }

            if ($headerValid && !$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $name = isset($row[$studentCol]) ? trim((string) $row[$studentCol]) : '';
            $classroom = isset($row[$classroomCol]) ? trim((string) $row[$classroomCol]) : '';
            if ($name === '' || $classroom === '') {
                continue;
            }

            $students[] = new StudentDTO(new FullNameDTO(...Lib::STRING::separateNames($name)), $classroom);
        }

        return array_values(array_unique($students));
    }

    /**
     * Finds a specified column index inside the header of a CSV file.
     * @param array $accepted The different descriptors of the column.
     * @param string[] $head The first row (header) of the CSV file.
     * @return ?int The index of the desired column.
     */
    public function getColIdx(array $accepted, array $head): ?int
    {
        $accepted = array_map(
            fn($v) => Lib::STRING::sanitize((string) $v, StringCase::LOWER),
            $accepted,
        );

        $col = null;
        foreach ($head as $idx => $name) {
            if (in_array($name, $accepted, true)) {
                $col = $idx;
                break;
            }
        }

        return $col;
    }
}
