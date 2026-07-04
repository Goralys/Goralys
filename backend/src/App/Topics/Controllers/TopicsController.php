<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Topics\Controllers;

use Goralys\App\HTTP\Files\Data\UploadedFileDTO;
use Goralys\App\HTTP\Files\GoralysFileManager;
use Goralys\App\Topics\Data\StudentDTO;
use Goralys\App\User\Data\UsernameTable;
use Goralys\Core\Topics\Config\TopicsImportConfig;
use Goralys\Core\Topics\Data\TopicDescriptorDTO;
use Goralys\Core\Topics\Data\TopicDTO;
use Goralys\Core\Topics\Repository\Interfaces\TopicsRepositoryInterface;
use Goralys\Core\Topics\Repository\TopicsRepository;
use Goralys\Core\Topics\Services\BuildFromCSVService;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Core\User\Repository\UserRepository;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\Exception\User\GoralysUserException;
use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\User\Data\FullNameDTO;

/**
 * Controller for managing topic-related operations, including CSV/ZIP imports and persistence.
 */
final class TopicsController
{
    private DbContainerInterface $db;
    private TopicsImportConfig $config;
    private TopicsRepositoryInterface $repo;
    private UserRepositoryInterface $users;
    private BuildFromCSVService $CSVBuilder;
    private GoralysFileManager $files;
    private UsernameTable $usernames;
    private int $nextId;

    /**
     * @param DbContainerInterface $db The injected DB.
     * @param UsernameTable $usernames The injected username table.
     * @param GoralysFileManager $files The injected file manager.
     */
    public function __construct(
        DbContainerInterface $db,
        UsernameTable $usernames,
        GoralysFileManager $files,
        LoggerInterface $logger,
    ) {
        $this->usernames = $usernames;

        $this->db = $db;
        $this->config = new TopicsImportConfig();

        $this->repo = new TopicsRepository($this->db);
        $this->users = new UserRepository($logger, $this->db);
        $this->CSVBuilder = new BuildFromCSVService($this->config);
        $this->nextId = 0;
        $this->files = $files;
    }

    /**
     * Inserts a TopicDTO into the database, including its students and teachers.
     * @param TopicDTO $topic The topic data transfer object to insert.
     * @return bool If the insertion succeeded.
     * @throws GoralysUserException
     */
    public function insert(TopicDTO $topic): bool
    {
        if (
            !$this->repo->insertTopic(
                $topic->id,
                $topic->code,
                $topic->name,
            )
        ) {
            return false;
        }

        if (
            array_any($topic->teachers, fn(FullNameDTO $t) => $this->users->whitelist($this->usernames->resolve($t), $t)
            && !$this->repo->insertTeacher(
                $topic->id,
                $this->usernames->resolve($t),
            ))
        ) {
            return false;
        }

        return array_all($topic->students, fn(StudentDTO $s) =>
                $this->users->whitelist(
                    $this->usernames->resolve($s->fullName),
                    $s->fullName
                )
                && $this->repo->insertStudent(
                    $topic->id,
                    $this->usernames->resolve($s->fullName),
                    $s->classroom
                ));
    }

    /**
     * Processes an uploaded ZIP file and creates TopicDTO objects from its content.
     * @param UploadedFileDTO $file The ZIP file metadata.
     * @return TopicDTO[] A list of constructed TopicDTO objects.
     * @throws GoralysRuntimeException If no valid topic files are found.
     */
    public function makeTopicsFromZip(UploadedFileDTO $file): array
    {
        $extractTo = $this->makeExtractionDir($file);
        $this->files->extract($file, $extractTo);

        $groupsToTeachers = $this->loadGroupsMapping($extractTo);

        $topics = [];

        foreach ($this->findTopicsCsv($extractTo) as $csvPath) {
            $descriptor = $this->parseTopicFilename($csvPath);
            if ($descriptor === null) {
                continue;
            }

            $students = $this->CSVBuilder->buildStudents($csvPath);
            $teachers = $groupsToTeachers[$descriptor->code] ?? [];

            $topics[] = $this->makeTopic($descriptor->name, $descriptor->code, $students, $teachers);
        }

        if (count($topics) === 0) {
            throw new GoralysRuntimeException("No topic CSV files found in ZIP (expected files like CODE_Name.csv).");
        }

        return $topics;
    }

    /**
     * Creates a temporary extraction directory for a ZIP file.
     * @param UploadedFileDTO $file The uploaded file metadata.
     * @return string The absolute path to the temporary directory.
     */
    private function makeExtractionDir(UploadedFileDTO $file): string
    {
        $baseDir = dirname($file->tmpPath);
        return $baseDir . DIRECTORY_SEPARATOR . "extracted_" . uniqid('', true) . DIRECTORY_SEPARATOR;
    }

    /**
     * Loads the group-to-teacher mapping from the 'groupes.csv' file in the extraction directory.
     * @param string $dir The path to the extraction directory.
     * @return array<string, list<string>> A mapping of group codes to teacher usernames.
     * @throws GoralysRuntimeException If the groups mapping file is missing.
     */
    private function loadGroupsMapping(string $dir): array
    {
        $groupsCsv = $dir . $this->config::GROUPS_FILENAME;
        if (!is_file($groupsCsv)) {
            throw new GoralysRuntimeException("Missing groups mapping file in ZIP (expected at: $groupsCsv)");
        }

        return $this->CSVBuilder->buildGroups($groupsCsv);
    }

    /**
     * Finds all CSV files in the extraction directory that are not the group mapping file.
     * @param string $dir The extraction directory path.
     * @return string[] List of absolute paths to topic CSV files.
     */
    private function findTopicsCsv(string $dir): array
    {
        $pattern = $dir . "*.csv";
        $csvFiles = [];
        foreach (glob($pattern) as $csv) {
            if (strtolower($csv) == $this->config::GROUPS_FILENAME) {
                continue;
            }
            $csvFiles[] = $csv;
        }

        return $csvFiles;
    }

    /**
     * Parses a topic CSV filename to extract its code and name.
     * Expected format: CODE_Name.csv
     * @param string $filePath The full path to the CSV file.
     * @return TopicDescriptorDTO|null The parsed descriptor or null if invalid.
     */
    private function parseTopicFilename(string $filePath): ?TopicDescriptorDTO
    {
        $base = basename($filePath);
        $filename = pathinfo($base, PATHINFO_FILENAME);
        $pos = strpos($filename, $this->config::TOPIC_CODE_NAME_SEPARATOR);

        if ($pos === false) {
            return null;
        }

        $code = trim(substr($filename, 0, $pos));
        $rawName = trim(substr($filename, $pos + 1));

        if ($code === '' || $rawName === '') {
            return null;
        }

        return new TopicDescriptorDTO($rawName, $code);
    }

    /**
     * Creates a new TopicDTO instance.
     * @param string $name The name of the topic.
     * @param string $code The code (ID) of the topic.
     * @param StudentDTO[] $students A list of student names or usernames.
     * @param string[] $teachers A list of teacher names or usernames.
     * @return TopicDTO The topic.
     */
    public function makeTopic(string $name, string $code, array $students, array $teachers): TopicDTO
    {
        $this->nextId++;
        return new TopicDTO(
            $this->nextId,
            $name,
            $code,
            array_map(fn (string $t) => new FullNameDTO(...Lib::STRING::separateNames($t)), $teachers),
            $students,
        );
    }

    /**
     * Writes all usernames associated with the full name of the user into a file.
     * @param TopicDTO[] $topics The topics to export the usernames for.
     * @return string The path to the file where the topics where exported.
     * @throws GoralysUserException
     */
    public function exportUsernames(array $topics): string
    {
        $out = "";
        foreach ($topics as $topic) {
            $head = "--------------- " . $topic->code . ": " . $topic->name . " ---------------";
            $out .= $head . PHP_EOL;

            $out .= "Professeurs:" . PHP_EOL;
            foreach ($topic->teachers as $teacher) {
                $out .= "    - " . $teacher . ": " . $this->usernames->resolve($teacher) . PHP_EOL;
            }

            $out .= "Élèves:" . PHP_EOL;
            foreach ($topic->students as $student) {
                $out .= "    - " . $student->fullName . ": " . $this->usernames->resolve($student->fullName) . PHP_EOL;
            }

            $out .= str_repeat("-", strlen($head)) . PHP_EOL;
        }

        $path = tempnam(sys_get_temp_dir(), "goralys_");
        file_put_contents($path, $out);

        return $path;
    }

    /**
     * Removes all topics and associated subjects from the database.
     * @return bool If the deletion was successful
     */
    public function clear(): bool
    {
        return $this->repo->clearAll();
    }
}
