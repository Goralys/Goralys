<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Repository;

use Goralys\App\Topics\Data\StudentDTO;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Data\SubjectsFilter;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Shared\Error\GoralysValueError;
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
     * Returns the list of all classrooms and the students they contain.
     * @return mysqli_result The query result.
     */
    public function getClassrooms(): mysqli_result
    {
        return $this->db->fetchNoArgs(
            "select 
                sc.class as classroom,
                pi.public_id as student
                from students_classroom sc
                join public_ids pi on sc.username = pi.username
            "
        );
    }

    /**
     * Returns the list of all topic groups and the students they contain.
     * @return mysqli_result The query result.
     */
    public function getTopicGroups(): mysqli_result
    {
        return $this->db->fetchNoArgs(
            "select 
                distinct pi.public_id as student, t.topic_code as topic
                from student_topics st
                join topics t on t.id = st.topic_id
                join public_ids pi on pi.username = st.student_username;
            "
        );
    }

    /**
     * @param SubjectsFilter $f The filter to apply to the subjects
     * @return mysqli_result All subjects in the database which fits the given filter.
     *
     * Please note that this functions automatically retrieves all subjects associated to a student if at least one of
     * his subjects fits the filter. This is mainly due to the fact that this function is mainly used during subjects
     * export. Thus, we need to have all the subjects for every student to do a proper export.
     */
    public function findFiltered(SubjectsFilter $f): mysqli_result
    {
        if (empty($f->status) || empty($f->classrooms) || empty($f->topics)) {
            throw new GoralysValueError("Invalid filter found: " . $f);
        }

        $statuses = array_map(fn(SubjectStatus $s) => $s->value, $f->status);
        $classrooms = $f->classrooms;
        $topics = $f->topics;
        // assume non-empty arrays because it would produce an error (SQL) anyway if the array was empty
        // (see error above)
        $aPlaceholder = fn (array $arr) => '?' . str_repeat(',?', count($arr) - 1);
        $aParam = fn (array $arr) => [implode("", array_map(fn(mixed $el) => is_int($el) ? 'i' : 's', $arr)), ...$arr];

        // just a simple SQL query
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
                GROUP_CONCAT(distinct tt.teacher_username order by tt.teacher_username separator ', ') as teachers
            from student_topics st
            join topics t on t.id = st.topic_id
            join topic_teachers tt on t.id = tt.topic_id
            where st.student_username in (
                select st.student_username from student_topics st
                join topics t on t.id = st.topic_id
                join students_classroom sc on sc.username = st.student_username
                    where st.subject_status in ({$aPlaceholder($statuses)})
                    and sc.class in ({$aPlaceholder($classrooms)})
                    and t.topic_code in ({$aPlaceholder($topics)})
            )
            group by st.student_username, st.topic_id 
            ",
            ...$aParam([...$statuses, ...$classrooms, ...$topics])
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
