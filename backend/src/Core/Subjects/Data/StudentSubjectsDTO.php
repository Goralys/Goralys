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
     * @param string $studentName
     * @param SpecialityDTO[] $subjects
     */
    public function __construct(
        public string $studentName,
        public array $subjects = [],
    ) {
    }
}
