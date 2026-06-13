<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Repository\Interfaces;

use Goralys\Shared\User\Data\FullNameDTO;

/**
 * Contract for the topic repository.
 * Covers insertion of topics, their teachers, and students, as well as bulk deletion.
 */
interface TopicsRepositoryInterface
{
    /**
     * @param int $topicId The unique ID of the topic.
     * @param string $topicCode The topic's code.
     * @param string $topicName The topic's display name.
     * @return bool If the insertion was successful or not.
     */
    public function insertTopic(int $topicId, string $topicCode, string $topicName): bool;

    /**
     * @param int $topicId The ID of the topic to attach the teacher to.
     * @param string $teacherUsername The teacher's username.
     * @return bool If the insertion was successful or not.
     */
    public function insertTeacher(int $topicId, string $teacherUsername): bool;

    /**
     * @param int $topicId The ID of the topic to attach the student to.
     * @param string $studentUsername The student's username.
     * @param FullNameDTO $fullName The student's "official" full name from the import data.
     * @param string $classroom The student's classroom (e.g. TA, TB, TF, etc.)
     * @return bool If the insertion was successful or not.
     */
    public function insertStudent(
        int $topicId,
        string $studentUsername,
        FullNameDTO $fullName,
        string $classroom
    ): bool;

    /**
     * Removes all topics and their associated records from the database.
     * @return bool If the deletion was successful or not.
     */
    public function clearAll(): bool;
}
