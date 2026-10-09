<?php

namespace Goralys\Core\Subjects\Data;

use Goralys\Core\Subjects\Data\Enums\SubjectStatus;

/**
 * This object is used to represent the different filters applicable when exporting subjects.
 */
final readonly class SubjectsFilter
{
    /**
     * @param SubjectStatus[] $status The statuses to export, every subject with one these statuses will be included.
     * @param string[] $classrooms The clssrooms to export, every subject belonging to a student in one of these
     * classrooms will be included.
     * @param string[] $topics The topics to export, every subject belonging to a student registered in one of these
     * topics will be included.
     *
     * **Note**: topic groups differ from classrooms, hence the need to distinguish them.
     */
    public function __construct(
        public array $status,
        public array $classrooms,
        public array $topics,
    ) {
    }

    public function __toString(): string
    {
        return "statuses: " . print_r($this->status, true) . PHP_EOL
               . "classrooms: " . print_r($this->classrooms, true) . PHP_EOL
               . "topics: " . print_r($this->topics, true) . PHP_EOL;
    }
}
