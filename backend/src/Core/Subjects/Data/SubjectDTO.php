<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data;

use DateTime;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use JsonSerializable;

/**
 * The DTO used to transport the data of a subject.
 * It is used inside the App and Core layers to get the subjects from the database.
 */
final readonly class SubjectDTO implements JsonSerializable
{
    /**
     * @param string $studentName The name of the student.
     * @param string $studentUsernameToken The frontend token used to retrieve the student's username.
     * @param string $subject The subject content.
     * @param SubjectStatus $status The current status of the subject.
     * @param string $comment The teacher's comment on the subject.
     * @param string $lastRejected The last rejected subject.
     * @param DateTime|null $lastUpdatedAt The date and time of the last status update.
     * @param string $topic The name of the topic associated with this subject.
     * @param string $topicCode The code of the topic associated with this subject.
     * @param string $teacherName The name of the assigned teacher.
     * @param string $teacherUsernameToken The frontend token used to retrieve the teacher's username.
     * @param bool $interdisciplinary Whether this subject is interdisciplinary.
     * @param bool $hasDraft Whether the student has a uploaded a draft file for this subject.
     */
    public function __construct(
        public string $studentName,
        public string $studentUsernameToken,
        public string $subject,
        public SubjectStatus $status,
        public string $comment,
        public string $lastRejected,
        public ?DateTime $lastUpdatedAt,
        public string $topic,
        public string $topicCode,
        public string $teacherName,
        public string $teacherUsernameToken,
        public bool $interdisciplinary,
        public bool $hasDraft = false,
    ) {
    }

    /**
     * Transforms the subject's data into a JSON array that is then sent to the frontend.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "student" => $this->studentName,
            "studentToken" => $this->studentUsernameToken,
            "subject" => $this->subject,
            "status" => $this->status->toString(),
            "comment" => $this->comment,
            "lastRejected" => $this->lastRejected,
            "topic" => $this->topic,
            "teacher" => $this->teacherName,
            "teacherToken" => $this->teacherUsernameToken,
            "hasDraft" => $this->hasDraft,
            "interdisciplinary" => $this->interdisciplinary,
        ];
    }
}
