<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Data;

/**
 * Data Transfer Object representing a Topic.
 */
final readonly class TopicDTO
{
    /**
     * @param int $id The unique ID of the topic.
     * @param string $name The name of the topic.
     * @param string $code The unique code for the topic.
     * @param string[] $teachers List of teacher usernames.
     * @param string[] $students List of student usernames.
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public array $teachers,
        public array $students,
    ) {
    }
}
