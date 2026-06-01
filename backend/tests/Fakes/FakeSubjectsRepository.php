<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use mysqli_result;

class FakeSubjectsRepository implements SubjectsRepositoryInterface
{
    private bool $updateResult = true;
    private mixed $getResult = null;

    /**
     * Set the result for update operations.
     * @param mixed $updateResult
     */
    public function setUpdateResult(mixed $updateResult): void
    {
        $this->updateResult = $updateResult;
    }

    /**
     * Set the result for get/find operations.
     * @param mixed $getResult
     */
    public function setGetResult(mixed $getResult): void
    {
        $this->getResult = $getResult;
    }

    public function findByStudent(string $studentUsername): mysqli_result
    {
        return $this->getResult;
    }

    public function findByTeacher(string $teacherUsername): mysqli_result
    {
        return $this->getResult;
    }

    public function findAll(): mysqli_result
    {
        return $this->getResult;
    }

    public function getStatus(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->getResult;
    }

    public function getDraftPath(string $teacherUsername, string $studentUsername, string $topic): mysqli_result
    {
        return $this->getResult;
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
}
