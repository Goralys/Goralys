<?php

namespace Goralys\Tests\Fakes;

use Goralys\App\Topics\Data\StudentDTO;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use mysqli_result;

class FakeSubjectsRepository implements SubjectsRepositoryInterface
{
    private bool $updateResult = true;
    private ?mysqli_result $queryResult = null;
    private ?StudentDTO $studentInfo = null;

    /**
     * Set the result for update operations.
     */
    public function setUpdateResult(bool $updateResult): void
    {
        $this->updateResult = $updateResult;
    }

    /**
     * Set the mysqli_result returned by the find/get query methods
     * (findByStudent, findByTeacher, findAll, getStatus, getDraftPath).
     */
    public function setQueryResult(mysqli_result $queryResult): void
    {
        $this->queryResult = $queryResult;
    }

    /**
     * Set the result returned by {@see getStudentInfo()}.
     */
    public function setStudentInfo(StudentDTO $studentInfo): void
    {
        $this->studentInfo = $studentInfo;
    }

    public function findByStudent(string $studentUsername): mysqli_result
    {
        return $this->queryResult;
    }

    public function findByTeacher(string $teacherUsername): mysqli_result
    {
        return $this->queryResult;
    }

    public function findAll(): mysqli_result
    {
        return $this->queryResult;
    }

    public function getStatus(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->queryResult;
    }

    public function getDraftPath(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->queryResult;
    }

    public function updateSubject(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newSubject,
        bool $interdisciplinary,
    ): bool {
        return $this->updateResult;
    }

    public function updateStatus(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        SubjectStatus $newStatus,
    ): bool {
        return $this->updateResult;
    }

    public function updateComment(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newComment,
    ): bool {
        return $this->updateResult;
    }

    /**
     * @param string $teacherUsername
     * @param string $studentUsername
     * @param string $topic
     * @param string $newPath
     * @return bool
     */
    public function updateDraftPath(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        string $newPath,
    ): bool {
        return $this->updateResult;
    }

    public function flushDraftPath(string $teacherUsername, string $studentUsername, string $topic): bool
    {
        return $this->updateResult;
    }

    public function getStudentInfo(string $username): StudentDTO
    {
        return $this->studentInfo;
    }
}
