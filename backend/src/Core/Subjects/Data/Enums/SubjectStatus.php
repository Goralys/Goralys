<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data\Enums;

/**
 * Represents the review lifecycle of a subject submission.
 */
enum SubjectStatus: int
{
    case UNKNOWN = -1;
    case NOT_SUBMITTED = 0;
    case SUBMITTED = 1;
    case REJECTED = 2;
    case APPROVED = 3;

    /**
     * @return string
     */
    public function toString(): string
    {
        return strtolower($this->name);
    }

    public static function fromString(string $status): self
    {
        return match ($status) {
            "not_submitted" => self::NOT_SUBMITTED,
            "submitted" => self::SUBMITTED,
            "rejected" => self::REJECTED,
            "approved" => self::APPROVED,
            default => self::UNKNOWN
        };
    }
}
