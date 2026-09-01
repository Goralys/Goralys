<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Drafts\Services;

use DirectoryIterator;
use Goralys\App\HTTP\Files\GoralysFileManager;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * Manages student draft file uploads: stores files on disk and records their paths in the database.
 */
final class StudentDraftsManager
{
    private LoggerInterface $logger;
    private SubjectsRepositoryInterface $repo;
    private GoralysFileManager $fileManager;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param SubjectsRepositoryInterface $repo The injected subjects repository.
     * @param GoralysFileManager $fileManager The injected file manager.
     */
    public function __construct(
        LoggerInterface $logger,
        SubjectsRepositoryInterface $repo,
        GoralysFileManager $fileManager,
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->fileManager = $fileManager;
    }

    /**
     * Updates a student's draft in the database.
     * @param string $studentUsername The student's username.
     * @param string $teacherUsername The teachers's username.
     * @param string $topicName The topic name.
     * @return bool If the draft was correctly updated or not.
     */
    public function update(string $studentUsername, string $teacherUsername, string $topicName): bool
    {
        $this->emptyDir($studentUsername, $teacherUsername);
        $uploadDir = realpath(Config::DIRECTORIES::ASSETS . "StudentsDrafts/$teacherUsername/$studentUsername/") ?: "";

        $file = $this->fileManager->get("draft-file");

        $extension = pathinfo($file->name, PATHINFO_EXTENSION) ?? "";
        $destination = $uploadDir . DIRECTORY_SEPARATOR . "draft." . $extension;

        if ($this->fileManager->move('draft-file', $destination)) {
            $this->logger->debug(
                LoggerInitiator::CORE,
                "Successfully moved uploaded draft for student: " . $studentUsername . ", with topic: " . $topicName,
            );

            return $this->repo->updateDraftPath(
                $teacherUsername,
                $studentUsername,
                $topicName,
                $destination,
            );
        }

        $this->logger->debug(
            LoggerInitiator::CORE,
            "Failed to move uploaded draft for student: " . $studentUsername . ", with topic: " . $topicName
                . " from: " . $file->tmpPath . ", to: " . $destination,
        );

        return false;
    }

    /**
     * Clears existing draft files for the given student/teacher pair and ensures the target directory exists.
     * @param string $studentUsername The student's username.
     * @param string $teacherUsername The teacher's username.
     * @return void
     */
    private function emptyDir(string $studentUsername, string $teacherUsername): void
    {
        $fullDir = Config::DIRECTORIES::ASSETS . "StudentsDrafts/$teacherUsername/$studentUsername/";

        if (is_dir($fullDir)) {
            foreach (new DirectoryIterator($fullDir) as $file) {
                if (
                    $file->isFile()
                    && pathinfo($file->getFilename(), PATHINFO_FILENAME) === "draft"
                    && in_array(pathinfo($file->getFilename(), PATHINFO_EXTENSION), ['txt', 'odt', 'docx'])
                ) {
                    unlink($file->getPathname());
                }
            }
        }
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0o777, true);
        }
    }

    /**
     * Deletes a student's draft in the database.
     * @param string $studentUsername The student's username.
     * @param string $teacherUsername The teachers's username.
     * @param string $topicName The topic name.
     * @return bool If the draft was correctly deleted or not.
     */
    public function flush(string $studentUsername, string $teacherUsername, string $topicName): bool
    {
        $this->emptyDir($studentUsername, $teacherUsername);

        if (!$this->repo->flushDraftPath($teacherUsername, $studentUsername, $topicName)) {
            $this->logger->debug(
                LoggerInitiator::CORE,
                "Failed to flush draft path (DB) for student: " . $studentUsername . ", with topic: " . $topicName
            );
            return false;
        }

        $this->logger->debug(
            LoggerInitiator::CORE,
            "Successfully deleted draft for student: " . $studentUsername . ", with topic: " . $topicName
        );

        return true;
    }

    /**
     * Retrieves the path to a student's draft
     * @param string $studentUsername The student's username.
     * @param string $teacherUsername The teachers's username.
     * @param string $topicName The topic name.
     * @return string The path to the student's draft file.
     * @throws GoralysRuntimeException If the draft could not be found.
     */
    public function getPath(string $studentUsername, string $teacherUsername, string $topicName): string
    {
        $result = $this->repo->getDraftPath($teacherUsername, $studentUsername, $topicName);
        if ($result->num_rows === 0) {
            throw new GoralysRuntimeException("Trying to access draft path for invalid teacher: " . $teacherUsername
            . ", student: " . $studentUsername . ", and topic: " . $topicName . " combination");
        }

        return $result->fetch_assoc()["path"];
    }
}
