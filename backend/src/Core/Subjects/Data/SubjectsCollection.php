<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Data;

use JsonSerializable;

/**
 * A special object used to represent an array of subjects.
 */
final class SubjectsCollection implements JsonSerializable
{
    /* @var SubjectDTO[] */
    public array $subjects = [];

    /**
     * Adds a new subject to the collection.
     * @param SubjectDTO $newSubject The subject to add.
     * @return void
     */
    public function addSubject(SubjectDTO $newSubject): void
    {
        $this->subjects = [...$this->subjects, $newSubject];
    }

    /**
     * Transforms the subject collection into a JSON array
     * @return SubjectDTO[]
     */
    public function jsonSerialize(): array
    {
        return $this->subjects;
    }
}
