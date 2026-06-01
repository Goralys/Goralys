<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Services;

use Goralys\Core\Subjects\Config\SubjectsExportConfig as Config;
use Goralys\Core\Subjects\Data\StudentSubjectsDTO;
use Goralys\Platform\Doc\PDF\Data\PdfSourceDTO;
use InvalidArgumentException;

/**
 * Renders the HTML export template for a student's subjects by injecting data into placeholders.
 * Supports simple variable substitution and inline ternary conditional expressions.
 */
final class SubjectsTemplateRenderer
{
    /**
     * Renders the PDF source (HTML + CSS) for a given student's subjects.
     * Resolves column widths, pathway detection, and all template variables before returning the source DTO.
     * @param StudentSubjectsDTO $student The student data to render.
     * @return PdfSourceDTO The rendered HTML and CSS ready for PDF generation.
     * @throws InvalidArgumentException If the student has fewer than 2 subjects.
     */
    public function render(StudentSubjectsDTO $student): PdfSourceDTO
    {
        if (count($student->subjects) < 2) {
            throw new InvalidArgumentException(
                "Student $student->studentName must have at least 2 subjects. Got: "
                . print_r($student->subjects, true),
            );
        }

        $html = file_get_contents(Config::TEMPLATE_SOURCE_PATH);
        $css = file_get_contents(Config::TEMPLATE_STYLES_PATH);

        $pos = strpos($student->studentName, " ");
        $firstname = $pos !== false ? substr($student->studentName, 0, $pos) : $student->studentName;
        $lastname  = $pos !== false ? substr($student->studentName, $pos + 1) : "";

        $colMm = [
            'label'  => 22,
            'intitulé' => 100,
            'spe'    => 45,
            'trans'  => 35,
        ];

        // Compute percentages that sum to exactly 100%
        $totalMm = array_sum($colMm);
        $pct = [];
        $assigned = 0;
        $keys = array_keys($colMm);
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $pct[$k] = round(100 - $assigned, 2);
            } else {
                $p = round($colMm[$k] / $totalMm * 100, 2);
                $pct[$k] = $p;
                $assigned += $p;
            }
        }

        $pathway = "générale";

        foreach (Config::getTechnologicalPathways() as $p) {
            foreach ($student->subjects as $subject) {
                if ($subject->topicCode && str_contains($subject->topicCode, $p->detectPattern)) {
                    $pathway = "technologique - " . $p->full;
                    break 2;
                }
            }
        }

        // CSS block injected alongside the main stylesheet
        $css .= PHP_EOL . PHP_EOL . "
            .questions-table { width: {$totalMm}mm; }
            .qt-col1 { width: {$pct['label']}%; }
            .qt-col2 { width: {$pct['intitulé']}%; }
            .qt-col3 { width: {$pct['spe']}%; }
            .qt-col4 { width: {$pct['trans']}%; }
            ";

        $vars = [
            'nom'    => $lastname,
            'prénom' => $firstname,
            'série'  => $pathway,
            'year'   => date("Y"),
        ];

        foreach ($student->subjects as $i => $subject) {
            $n = $i + 1;
            $vars["spe$n"]              = $subject->speciality;
            $vars["prof$n"]             = $subject->teacherName;
            $vars["q$n"]                = $subject->subject;
            $vars["dateQ$n"]            = $subject->validatedAt->format("d/m/Y");
            $vars["interdisciplinary$n"] = $subject->interdisciplinary;
        }

        $html = $this->resolveConditionals($html, $vars);

        // Simple replacements
        $replacements = array_combine(
            array_map(fn($k) => "{{{$k}}}", array_keys($vars)),
            array_values($vars),
        );
        $html = strtr($html, $replacements);

        return new PdfSourceDTO($html, $css);
    }

    /**
     * Resolves inline ternary conditionals of the form `{{ [not ]varName ? 'ifTrue' : 'ifFalse' }}` in an HTML string.
     * @param string $html The HTML template content.
     * @param array $vars The variable map used to evaluate conditions.
     * @return string The HTML with all conditionals replaced by their resolved values.
     */
    private function resolveConditionals(string $html, array $vars): string
    {
        // Ternary operator match
        return preg_replace_callback(
            '/\{\{\s*(not\s+|!)?(\w+)\s*\?\s*["\']([^"\']*)["\']?\s*:\s*["\']([^"\']*)["\']?\s*}}/',
            function (array $matches) use ($vars): string {
                [, $negation, $varName, $ifTrue, $ifFalse] = $matches;
                $value = $vars[$varName] ?? null;
                if ($negation !== '') {
                    $value = !$value;
                }
                return $value ? $ifTrue : $ifFalse;
            },
            $html,
        );
    }
}
