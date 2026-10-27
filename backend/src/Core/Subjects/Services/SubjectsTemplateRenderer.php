<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Services;

use Goralys\Core\Subjects\Config\SubjectsExportConfig as Config;
use Goralys\Core\Subjects\Data\StudentSubjectsDTO;
use InvalidArgumentException;

/**
 * Renders the HTML export template for a student's subjects by injecting data into placeholders.
 * Supports simple variable substitution and inline ternary conditional expressions.
 */
final class SubjectsTemplateRenderer
{
    /**
     * Adds a given student's subjects to a batch.
     * Resolves all template variables before adding them to the batch.
     * @param array $batch The batch to put the result into.
     * @param StudentSubjectsDTO $student The student data to render.
     * @param string $out The path to export the subjects to (output file).
     * @return void
     * @throws InvalidArgumentException If the student has fewer than 2 subjects.
     */
    public function addToBatch(array &$batch, StudentSubjectsDTO $student, string $out): void
    {
        // if (count($student->subjects) < 2) {
        //     throw new InvalidArgumentException(
        //         "Student {$student->student->fullName} must have at least 2 subjects. Got: "
        //         . print_r($student->subjects, true),
        //     );
        // }

        $pathway = "générale";

        foreach (Config::getTechnologicalPathways() as $p) {
            foreach ($student->subjects as $subject) {
                if ($subject->topicCode && str_contains($subject->topicCode, $p->detectPattern)) {
                    $pathway = "technologique - " . $p->full;
                    break 2; // break both outer loops
                }
            }
        }

        $vars = [
            'nom'    => $student->student->fullName->last,
            'prénom' => $student->student->fullName->first,
            'série'  => $pathway,
            'year'   => date("Y"),
        ];
        $flags = [];

        foreach ($student->subjects as $i => $subject) {
            $n = $i + 1;
            $vars["spe$n"]              = $subject->speciality;
            $vars["prof$n"]             = $subject->teacherName;
            $vars["q$n"]                = $subject->subject;
            $vars["dateQ$n"]            = $subject->validatedAt->format("d/m/Y");
            $flags["interdisciplinary$n"] = $subject->interdisciplinary;
        }

        if (!isset($batch['jobs'])) {
            $batch['jobs'] = [];
        }

        $batch['jobs'][] = [
            'output_file' => $out,
            'vars' => $vars,
            'flags' => $flags
        ];
    }
}
