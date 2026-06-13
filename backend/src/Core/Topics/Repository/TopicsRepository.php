<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Repository;

use Goralys\Core\Topics\Repository\Interfaces\TopicsRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Shared\User\Data\FullNameDTO;

/**
 * Repository class for handling database operations related to Topics.
 */
final class TopicsRepository implements TopicsRepositoryInterface
{
    private DbContainerInterface $db;

    /**
     * @param DbContainerInterface $db The injected DB.
     */
    public function __construct(
        DbContainerInterface $db,
    ) {
        $this->db = $db;
    }

    /**
     * Inserts a new topic into the 'topics' table.
     * @param int $topicId The unique ID of the topic.
     * @param string $topicCode The unique code for the topic.
     * @param string $topicName The display name of the topic.
     * @return bool If the insertion succeeded.
     */
    public function insertTopic(int $topicId, string $topicCode, string $topicName): bool
    {
        return $this->db->run(
            "insert into topics (id, topic_code, name) values (?, ?, ?)",
            "iss",
            $topicId,
            $topicCode,
            $topicName,
        );
    }

    /**
     * Associates a teacher with a topic in the 'topic_teachers' table.
     * @param int $topicId The ID of the topic.
     * @param string $teacherUsername The username of the teacher.
     * @return bool If the insertion succeeded.
     */
    public function insertTeacher(int $topicId, string $teacherUsername): bool
    {
        return $this->db->run(
            "insert into topic_teachers (topic_id, teacher_username) values (?, ?)",
            "is",
            $topicId,
            $teacherUsername,
        );
    }

    /**
     * Associates a student with a topic in the 'student_topics' table.
     * @param int $topicId The ID of the topic to attach the student to.
     * @param string $studentUsername The student's username.
     * @param FullNameDTO $fullName The student's "official" full name from the import data.
     * @param string $classroom The student's classroom.
     * @return bool If the insertion succeeded.
     */
    public function insertStudent(int $topicId, string $studentUsername, FullNameDTO $fullName, string $classroom): bool
    {
        return $this->db->run(
            "insert into student_topics 
                   (student_username, topic_id, subject, last_rejected, teacher_comment, draft_path, subject_status)
                   values (?, ?, null, null, null, null, 0)",
            "si",
            $studentUsername,
            $topicId,
        ) && $this->db->run(
            "insert ignore into student_info (username, firstname, lastname, class) values (?, ?, ?, ?)",
            "ssss",
            $studentUsername,
            $fullName->first,
            $fullName->last,
            $classroom
        );
    }

    /**
     * Removes all topics and associated subjects from the database.
     * @return bool If the deletion was successful
     */
    public function clearAll(): bool
    {
        $tables = [
            "student_topics",
            "student_info",
            "topic_teachers",
            "topics",
        ];

        $this->db->runNoArgs(
        /** @lang SQL */
            "set foreign_key_checks = 0"
        );
        try {
            foreach ($tables as $table) {
                $this->db->runNoArgs(
                    /** @lang SQL */
                    "truncate table `$table`",
                );
            }
        } finally {
            $this->db->runNoArgs(
            /** @lang SQL */
                "set foreign_key_checks = 0"
            );
        }

        return true;
    }
}
