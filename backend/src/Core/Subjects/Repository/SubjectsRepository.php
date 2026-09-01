<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Repository;

use Goralys\App\Topics\Data\StudentDTO;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Shared\Exception\User\UserNotFoundException;
use Goralys\Shared\User\Data\FullNameDTO;
use mysqli_result;

/**
 * The repository to fetch and modify subjects inside the database.
 */
final class SubjectsRepository implements SubjectsRepositoryInterface
{
    private DbContainerInterface $db;

    /**
     * Initializes the database container for the repository.
     * @param DbContainerInterface $db
     */
    public function __construct(DbContainerInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Gets all the subjects for a given student.
     * @param string $studentUsername The student's username.
     * @return mysqli_result The result of the request.
     */
    public function findByStudent(string $studentUsername): mysqli_result
    {
        return $this->db->fetch(
            "select
                st.subject,
                st.subject_status,
                st.teacher_comment as comment,
                st.last_rejected,
                st.is_interdisciplinary,
                st.last_updated_at,
                t.name as topic,
                t.topic_code as topic_code,
                GROUP_CONCAT(distinct tt.teacher_username order by tt.teacher_username separator ', ') as teachers
            from student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            where st.student_username = ?
            group by st.student_username, st.topic_id",
            "s",
            $studentUsername,
        );
    }

    /**
     * Gets all the subjects for a given teacher.
     * @param string $teacherUsername The teacher's username.
     * @return mysqli_result The result of the request.
     * */
    public function findByTeacher(string $teacherUsername): mysqli_result
    {
        return $this->db->fetch(
            "select
                st.student_username as student,
                st.subject,
                st.subject_status,
                st.teacher_comment as comment,
                st.last_rejected,
                st.is_interdisciplinary,
                st.last_updated_at,
                t.name as topic,
                t.topic_code as topic_code,
                st.draft_path as draftPath,
                GROUP_CONCAT(distinct tt.teacher_username order by tt.teacher_username separator ', ') as teachers
            from topics t
            join student_topics st on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            where tt.teacher_username = ?
            group by st.student_username, st.topic_id",
            "s",
            $teacherUsername,
        );
    }

    /**
     * Gets all the subjects from the database.
     * This should only be used for admin accounts.
     * @return mysqli_result The result of the request.
     */
    public function findAll(): mysqli_result
    {
        return $this->db->fetchNoArgs(
            "select
                st.student_username as student,
                st.subject,
                st.subject_status,
                st.teacher_comment as comment,
                st.last_rejected,
                st.is_interdisciplinary,
                st.last_updated_at,
                t.name as topic,
                t.topic_code as topic_code,
                GROUP_CONCAT(distinct tt.teacher_username order by tt.teacher_username separator ', ') as teachers
            from topics t
            join topic_teachers tt on t.id = tt.topic_id
            join student_topics st on t.id = st.topic_id
            group by st.student_username, st.topic_id",
        );
    }

    /**
     * Get the status of a given subject.
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The name of the topic.
     * @return mysqli_result The result of the request.
     */
    public function getStatus(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->db->fetch(
            "select st.subject_status as status
            from topics t
            join student_topics st on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            where tt.teacher_username = ?
              and st.student_username = ?
              and t.name = ?",
            "sss",
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }

    /**
     * Get the path to a student's draft for a given subject.
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The name of the topic.
     * @return mysqli_result The result of the request.
     */
    public function getDraftPath(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->db->fetch(
            "select st.draft_path as path
            from topics t
            join student_topics st on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            where tt.teacher_username = ?
              and st.student_username = ?
              and t.name = ?",
            "sss",
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }

    /**
     * Retrieves a student's information from the database (classroom and "official" name).
     * @param string $username The username of the target student.
     * @return StudentDTO The student's fullname and classroom.
     * @throws UserNotFoundException If the provided username is invalid.
     */
    public function getStudentInfo(string $username): StudentDTO
    {
        $result = $this->db->fetch(
            "select firstname, lastname, class 
                   from students_classroom sc
                   right join users_info ui on ui.username = sc.username
                   where sc.username = ?",
            "s",
            $username
        );

        if ($result->num_rows === 0) {
            throw new UserNotFoundException("Invalid student username: " . $username);
        }

        $row = $result->fetch_assoc();
        return new StudentDTO(new FullNameDTO($row['firstname'], $row['lastname']), $row['class']);
    }

    /**
     * Update a subject's content inside the database.
     * A subject is always identified by the combination of three variables: the teacher, the student, and the topic.
     * @param string $teacherUsername The student's username.
     * @param string $studentUsername The teacher's username.
     * @param string $topic The name of the topic.
     * @param string $newSubject The new subject.
     * @param bool $interdisciplinary If this new subject is interdiscplinary or not.
     * @return bool If the update was successful or not.
     */
    public function updateSubject(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newSubject,
        bool $interdisciplinary,
    ): bool {
        return $this->db->run(
            "update student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            set st.subject = ?, st.subject_status = 0, is_interdisciplinary = ?
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?
            and (st.subject_status = 0 or st.subject_status = 2)",
            "sisss",
            $newSubject,
            $interdisciplinary,
            $teacherUsername,
            $studentUsername,
            $topic,
        ) || $this->db->fetch(
            "select 1 from student_topics st 
            join topics t on st.topic_id = t.id
            join topic_teachers tt on t.id = tt.topic_id
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?
            and (st.subject_status = 0 or st.subject_status = 2)
            and st.subject = ?",
            "ssss",
            $teacherUsername,
            $studentUsername,
            $topic,
            $newSubject
        )->num_rows > 0; // check if the subject was already the same, in which case return true
        // note: this query always returns at least 1 row unless the first one fails
    }

    /**
     * Update a subject's status inside the database.
     * A subject is always identified by the combination of three variables: the teacher, the student, and the topic.
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The name of the topic.
     * @param SubjectStatus $newStatus The new status of the subject.
     * @return bool If the update was successful or not.
     */
    public function updateStatus(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        SubjectStatus $newStatus,
    ): bool {
        return $this->db->run(
            "update student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            set st.subject_status = ?,
                st.last_rejected = IF(? = 2, st.subject, st.last_rejected)
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?",
            "iisss",
            $newStatus->value,
            $newStatus->value,
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }

    /**
     * Update a subject's comment inside the database.
     * The comment is written by the teacher and seen by both the teacher and the student.
     * A subject is always identified by the combination of three variables: the teacher, the student, and the topic.
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The name of the topic.
     * @param string $newComment The new comment for the subject.
     * @return bool If the update was successful or not.
     */
    public function updateComment(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newComment,
    ): bool {
        return $this->db->run(
            "update student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            set st.teacher_comment = ?
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?",
            "ssss",
            $newComment,
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }

    /**
     * Update a subject's draft path inside the database.
     * The comment is written by the teacher and seen by both the teacher and the student.
     * A subject is always identified by the combination of three variables: the teacher, the student, and the topic.
     * @param string $teacherUsername The teacher's username.
     * @param string $studentUsername The student's username.
     * @param string $topic The name of the topic.
     * @param string$newPath The new path to the student's draft.
     * @return bool If the update was successful or not.
     */
    public function updateDraftPath(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newPath,
    ): bool {
        return $this->db->run(
            "update student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            set st.draft_path = ?
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?",
            "ssss",
            $newPath,
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }

    public function flushDraftPath(string $teacherUsername, string $studentUsername, string $topic): bool
    {
        return $this->db->runIgnoreNoOps(
            "update student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            set st.draft_path = null
            where tt.teacher_username = ?
            and st.student_username = ?
            and t.name = ?",
            "sss",
            $teacherUsername,
            $studentUsername,
            $topic,
        );
    }
}
