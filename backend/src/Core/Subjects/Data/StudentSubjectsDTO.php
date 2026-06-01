<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data;

/**
 * DTO grouping all speciality entries for a single student, used in PDF exports.
 */
final readonly class StudentSubjectsDTO
{
    /**
     * @param string $studentName The name of the student.
     * @param SpecialityDTO[] $subjects The list of the student's subjects
     * @param string $exportSuffix A special flag to indicate a special suffix to append to the export folder.
     * This flag is optionnal and is mainly used to give better indications when exporting the PDFs. This way,
     * we can, for exemple, put these exports in a subfolder (e.g. 'broken-student', 'broken-teacher', etc.),
     * to indicate manual checking might be necessary.
     */
    public function __construct(
        public string $studentName,
        public array $subjects = [],
        public string $exportSuffix = "",
    ) {
    }
}
