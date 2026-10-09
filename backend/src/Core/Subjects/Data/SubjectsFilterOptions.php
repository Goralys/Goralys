<?php

namespace Goralys\Core\Subjects\Data;

use JsonSerializable;

/**
 * This DTO represents the options a user has when exporting the subjects and creating a {@see SubjectsFilter}.
 */
final readonly class SubjectsFilterOptions implements JsonSerializable
{
    /**
     * @param string[][] $classrooms All avalaible classrooms.
     * @param string[][] $topics All avalaible topics.
     */
    public function __construct(
        public array $classrooms,
        public array $topics,
    ) {
    }

    /**
     * Transforms the options into a comprehensive JSON array that can be sent to the frontend.
     * @return array The formatted data.
     */
    public function jsonSerialize(): array
    {
        return [
            'classrooms' => $this->classrooms,
            'topics' => $this->topics,
        ];
    }
}
