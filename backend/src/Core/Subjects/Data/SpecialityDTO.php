<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data;

use DateTime;

/**
 * DTO representing a single speciality entry for a student, as used in PDF exports.
 */
final readonly class SpecialityDTO
{
    /**
     * @param string $teacherName The full name of the assigned teacher.
     * @param string $speciality The speciality name.
     * @param string $topicCode The code of the associated topic.
     * @param string $subject The validated subject title or content.
     * @param DateTime $validatedAt The date and time the subject was validated.
     * @param bool $interdisciplinary Whether the subject is interdisciplinary.
     */
    public function __construct(
        public string $teacherName,
        public string $speciality,
        public string $topicCode,
        public string $subject,
        public DateTime $validatedAt,
        public bool $interdisciplinary,
    ) {
    }
}
