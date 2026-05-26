<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Subjects\Controllers;

use DateMalformedStringException;
use DateTime;
use Goralys\App\HTTP\Files\GoralysFileManager;
use Goralys\App\Subjects\Data\Enums\SubjectFields;
use Goralys\App\User\Services\UsernameManager;
use Goralys\Core\Drafts\Services\StudentDraftsManager;
use Goralys\Core\Subjects\Config\SubjectsExportConfig;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Data\SpecialityDTO;
use Goralys\Core\Subjects\Data\StudentSubjectsDTO;
use Goralys\Core\Subjects\Data\SubjectDTO;
use Goralys\Core\Subjects\Data\SubjectsCollection;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Core\Subjects\Repository\SubjectsRepository;
use Goralys\Core\Subjects\Services\GetSubjectsService;
use Goralys\Core\Subjects\Services\SubjectsTemplateRenderer;
use Goralys\Core\Subjects\Services\UpdateSubjectService;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Core\User\Repository\UserRepository;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Doc\PDF\Interfaces\PdfExporterInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;
use ZipArchive;

/**
 * The controller used to update/get subjects from the database via the {@see SubjectsRepository}
 * (and intermediate services)
 */
final class SubjectsController
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;
    private SubjectsRepositoryInterface $repo;
    private UpdateSubjectService $updateService;
    private UsernameFormatterService $formatter;
    private UsernameManager $usernameManager;
    public StudentDraftsManager $draftsManager;
    private GoralysFileManager $fileManager;
    private GetSubjectsService $getService;
    private UserRepositoryInterface $userRepo;
    private SubjectsTemplateRenderer $renderer;
    private SubjectsExportConfig $exportConfig;
    private PdfExporterInterface $exporter;

    /**
     * Initializes the logger and database container for the controller.
     * Also instantiates all of its sub-services.
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected db.
     * @param GoralysFileManager $fileManager The injected file manager.
     * @param PdfExporterInterface $exporter The injected PDF exporter.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
        GoralysFileManager $fileManager,
        PdfExporterInterface $exporter,
    ) {
        $this->logger = $logger;
        $this->db = $db;

        $this->repo = new SubjectsRepository($this->db);
        $this->userRepo = new UserRepository($this->logger, $this->db);
        $this->formatter = new UsernameFormatterService();
        $this->usernameManager = new UsernameManager($this->userRepo);
        $this->fileManager = $fileManager;
        $this->draftsManager = new StudentDraftsManager($this->logger, $this->repo, $this->fileManager);
        $this->updateService = new UpdateSubjectService($this->logger, $this->repo);
        $this->getService = new GetSubjectsService(
            $this->logger,
            $this->repo,
            $this->formatter,
            $this->usernameManager,
        );
        $this->exportConfig = new SubjectsExportConfig();
        $this->exporter = $exporter;
        $this->renderer = new SubjectsTemplateRenderer($this->exportConfig);
    }

    /**
     * Update a given field for a teacher and student pair.
     * @param string $teacherUsername The username of the teacher.
     * @param string $studentUsername The username of the student.
     * @param string $topic The name of the topic.
     * @param SubjectFields $field The field to update.
     * @param string|SubjectStatus $newValue The new value of the field.
     * @param bool|null $interdisciplinary Only used when updating the subject.
     * @return bool If the update was successful or not.
     */
    public function updateField(
        string $teacherUsername,
        string $studentUsername,
        string $topic,
        SubjectFields $field,
        string|SubjectStatus $newValue,
        ?bool $interdisciplinary = null,
    ): bool {
        return match ($field) {
            SubjectFields::SUBJECT => $this->updateService->updateSubject(
                $teacherUsername,
                $studentUsername,
                $topic,
                $newValue,
                $interdisciplinary,
            ),
            SubjectFields::STATUS => $this->updateService->updateSubjectStatus(
                $teacherUsername,
                $studentUsername,
                $topic,
                $newValue,
            ),
            SubjectFields::COMMENT => $this->updateService->updateComment(
                $teacherUsername,
                $studentUsername,
                $topic,
                $newValue,
            ),
        };
    }

    /**
     * Get the subjects for a given user with a given role.
     * @param UserRole $role The role of the user to get the subjects of.
     * Let the defaults value ("") for admins as they have access to all subjects.
     * @return SubjectsCollection The list of the retrieved subjects.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the subjects cannot be retrived, refer to the exact implementation for more
     * details.
     */
    public function getForRole(UserRole $role): SubjectsCollection
    {
        $username = $_SESSION[GoralysConfig::SESSION::USERNAME];

        return match ($role) {
            UserRole::STUDENT => $this->getService->getStudentSubjects($username),
            UserRole::TEACHER => $this->getService->getTeacherSubjects($username),
            UserRole::ADMIN => $this->getService->getAllSubjects(),
            UserRole::UNKNOWN => new SubjectsCollection(),
        };
    }

    /**
     * Get the status of a given subject.
     * @param string $teacherUsername The username of the teacher.
     * @param string $studentUsername The username of the student.
     * @param string $topic The name of the topic.
     * @return SubjectStatus The status of the subject.
     */
    public function getStatus(string $teacherUsername, string $studentUsername, string $topic): SubjectStatus
    {
        $result = $this->repo->getStatus($teacherUsername, $studentUsername, $topic);

        $status = $result->fetch_assoc()['status'];

        return SubjectStatus::from($status);
    }

    /**
     * Groups the given subjects by students.
     * @param SubjectsCollection $subjects The subjects to group.
     * @return StudentSubjectsDTO[] The students associated with their subjects.
     * @throws GoralysRuntimeException If the username resolution fails.
     */
    private function groupByStudents(SubjectsCollection $subjects): array
    {
        /** @var SubjectDTO[][] $grouped */
        $grouped = [];
        foreach ($subjects->subjects as $subject) {
            $grouped[$this->usernameManager->get($subject->studentUsernameToken)][] = $subject;
        }

        return $grouped
                    |> array_keys(...)
                    |> (fn($x) => array_map(
                        /**
                         * @param SubjectDTO[] $subjects
                         * @return StudentSubjectsDTO
                         * @throws GoralysRuntimeException
                         */
                        function (string $username, array $subjects) {

                            $specialities = [];
                            foreach ($subjects as $subject) {
                                $teacherUsername =  $this->usernameManager->get($subject->teacherUsernameToken);
                                // Use reversed formatted name (J. DOE instead of DOE J.) for consistency
                                // between 'real' users (DB names) and 'virtual' users (formatted names)
                                $specialities[] = new SpecialityDTO(
                                    $this->userRepo->getFullNameForUsername(
                                        $teacherUsername,
                                    ) ?? $this->formatter->formatUsername($teacherUsername, true),
                                    $subject->topic,
                                    $subject->topicCode,
                                    $subject->subject,
                                    $subject->lastUpdatedAt ?? new DateTime(),
                                    $subject->interdisciplinary,
                                );
                            }
                            return new StudentSubjectsDTO(
                                $this->userRepo->getFullNameForUsername($username) ?? $this->formatter->formatUsername($username, true),
                                $specialities,
                            );
                        },
                        $x,
                        $grouped,
                    ))
                    |> array_values(...);
    }

    /**
     * Exports all subjects in the given collection.
     * @param SubjectsCollection $subjects The subjects to export.
     * @return string The path to the generated zip file.
     * @throws GoralysRuntimeException If the zip export goes wrong.
     */
    public function exportAll(SubjectsCollection $subjects): string
    {
        $grouped = $this->groupByStudents($subjects);
        $exportedPaths = [];

        foreach ($grouped as $s) {
            $filename = $this->exportConfig::EXPORT_BASE_NAME . date("Y") . " - " . $s->studentName . ".pdf";
            $filePath = $this->exportConfig::ASSETS_PATH . "Exports/" . $filename;

            $pdf = $this->renderer->render($s);
            $this->exporter->export(
                $pdf,
                $filePath,
                $this->exportConfig::ASSETS_PATH,
            );

            $exportedPaths[] = $filePath;
        }

        return $this->zipExports($exportedPaths);
    }

    /**
     * Deletes all exported files (PDFs and zips) from the exports directory.
     * @return void
     * @throws GoralysRuntimeException If the exports directory does not exist or a file cannot be deleted.
     */
    public function cleanExports(): void
    {
        $exportsDir = $this->exportConfig::ASSETS_PATH . "Exports/";

        if (!is_dir($exportsDir)) {
            throw new GoralysRuntimeException("Exports directory not found at: $exportsDir");
        }

        $files = array_merge(glob($exportsDir . "*.pdf"), glob($exportsDir . "*.zip"));

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            if (!unlink($file)) {
                throw new GoralysRuntimeException("Failed to delete export file: $file");
            }
        }
    }

    /**
     * Zips a list of exported PDF files into a single archive.
     * @param string[] $filePaths The list of PDF file paths to zip.
     * @return string The path to the generated zip file.
     * @throws GoralysRuntimeException If the zip archive could not be created or a file is missing.
     */
    private function zipExports(array $filePaths): string
    {
        $zipPath = $this->exportConfig::ASSETS_PATH . "Exports/export_" . date("Y-m-d_His") . ".zip";

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new GoralysRuntimeException("Could not create zip archive at: $zipPath");
        }

        foreach ($filePaths as $path) {
            if (!file_exists($path)) {
                $zip->close();
                throw new GoralysRuntimeException("File not found when zipping: $path");
            }
            $zip->addFile($path, basename($path));
        }

        $zip->close();

        return $zipPath;
    }
}
