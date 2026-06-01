<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Config;

use Goralys\Core\Subjects\Data\PathwayDTO;

/**
 * Configuration class for the subject export process.
 */
final class SubjectsExportConfig
{
    public const string ASSETS_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR
        . 'Template' . DIRECTORY_SEPARATOR;
    public const string TEMPLATE_SOURCE_PATH = self::ASSETS_PATH . 'main.html';
    public const string TEMPLATE_STYLES_PATH = self::ASSETS_PATH . 'style.css';
    public const string EXPORT_BASE_NAME     = 'FICHE_GO-';
    public const string EXPORT_BASE_DIR     = self::ASSETS_PATH . 'Exports' . DIRECTORY_SEPARATOR;
    public const string EXPORT_BROKEN_TEACHER_DIR = 'Manquants-Prof' . DIRECTORY_SEPARATOR;
    public const string EXPORT_BROKEN_STUDENT_DIR = 'Manquants-Élève' . DIRECTORY_SEPARATOR;
    public const string EXPORT_BROKEN_BOTH_DIR = 'Manquants-Prof&Élève' . DIRECTORY_SEPARATOR;

    public const array EXPORT_BROKEN_DIRS = array(
            self::EXPORT_BROKEN_STUDENT_DIR,
            self::EXPORT_BROKEN_TEACHER_DIR,
            self::EXPORT_BROKEN_BOTH_DIR
    );

    /**
     * @return PathwayDTO[]
     */
    public static function getTechnologicalPathways(): array
    {
        return [
            new PathwayDTO(
                full: 'Sciences et Technologies du Management et de la Gestion',
                detectPattern: 'STMG',
            ),
        ];
    }

    /**
     * Returns the broken dir suffix to append to an export path.
     * @param bool $studentMissing If the student is missing.
     * @param bool $teacherMissing If the teacher is missing
     * @return string The suffix.
     */
    public static function determineBrokenDir(bool $studentMissing, bool $teacherMissing): string
    {
        if ($studentMissing && $teacherMissing) {
            return self::EXPORT_BROKEN_BOTH_DIR;
        }
        if ($studentMissing) {
            return self::EXPORT_BROKEN_STUDENT_DIR;
        }
        return $teacherMissing ? self::EXPORT_BROKEN_TEACHER_DIR : "";
    }
}
