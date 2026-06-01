<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Repository\Interfaces;

use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use mysqli_result;

/**
 * Contract for the subject repository.
 * Exposes query and update operations on the `student_topics` table.
 */
interface SubjectsRepositoryInterface
{
    // Queries
    /**
     * @param string $studentUsername The student's username.
     * @return mysqli_result The subjects belonging to the given student.
     */
    public function findByStudent(string $studentUsername): mysqli_result;

    /**
     * @param string $teacherUsername The teacher's username.
     * @return mysqli_result The subjects supervised by the given teacher.
     */
    public function findByTeacher(string $teacherUsername): mysqli_result;

    /**
     * @return mysqli_result All subjects in the database.
     */
    public function findAll(): mysqli_result;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @return mysqli_result The current status for the given subject.
     */
    public function getStatus(string $teacherUsername, string $studentUsername, string $topic): mysqli_result;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @return mysqli_result The path to the student's draft for the given subject.
     */
    public function getDraftPath(string $teacherUsername, string $studentUsername, string $topic): mysqli_result;

    // Updates
    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @param string $newSubject The new subject content.
     * @param bool $interdisciplinary Whether the subject is interdisciplinary.
     * @return bool If the update was successful or not.
     */
    public function updateSubject(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newSubject,
        bool $interdisciplinary,
    ): bool;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @param SubjectStatus $newStatus The new status.
     * @return bool If the update was successful or not.
     */
    public function updateStatus(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        SubjectStatus $newStatus,
    ): bool;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @param string $newComment The new teacher comment.
     * @return bool If the update was successful or not.
     */
    public function updateComment(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newComment,
    ): bool;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @param string $newPath The new path to the student's draft file.
     * @return bool If the update was successful or not.
     */
    public function updateDraftPath(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newPath,
    ): bool;

    /**
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The topic name.
     * @return bool If the update was successful or not.
     */
    public function flushDraftPath(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
    ): bool;
}
