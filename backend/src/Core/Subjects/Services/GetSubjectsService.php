<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Services;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Data\SubjectDTO;
use Goralys\Core\Subjects\Data\SubjectsCollection;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;
use mysqli_result;

/**
 * The service used to fetch subjects inside the database via the subject repository
 */
final class GetSubjectsService
{
    private LoggerInterface $logger;
    private SubjectsRepositoryInterface $repo;
    private UsernameFormatterService $formatter;
    private UsernameManager $usernameManager;

    /**
     * Initializes the logger, database container and a utility service - the username formatter - used by the service.
     * @param LoggerInterface $logger The injected logger
     * @param SubjectsRepositoryInterface $repo The injected subjects repository
     * @param UsernameFormatterService $formatter The injected username formatter
     * This formatter is used to transform the usernames stored inside the database with the format f.lastnameX to
     * LASTNAME. F with f the first letter of the first name.
     * This is useful to avoid displaying private usernames to other users.
     * Therefore, as the username format is very specific to the project, you may need to tweak the username formatter
     * or get rid of it completely depending on your project.
     */
    public function __construct(
        LoggerInterface $logger,
        SubjectsRepositoryInterface $repo,
        UsernameFormatterService $formatter,
        UsernameManager $usernameManager,
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->formatter = $formatter;
        $this->usernameManager = $usernameManager;
    }

    /**
     * A simple helper to transform a request result from the repository into a usable subjects collection.
     * This version is specific to student subjects.
     * There are three declinations of this function for students, teachers and admins (all) as the formatting varies
     * from one role to another as the inputs are different.
     * @param mysqli_result $result The result of the request from the repository.
     * @param string $studentUsername The student's username.
     * @return SubjectsCollection The array containing all the subjects in a more usable format.
     * The `SubjectsCollection` is also used here as it implements a custom way to transform it into a JSON array and
     * thus make the output process ot the frontend more straightforward.
     * @throws DateMalformedStringException|GoralysRuntimeException
     */
    private function formatStudentSubjects(mysqli_result $result, string $studentUsername): SubjectsCollection
    {
        $subjects = new SubjectsCollection();

        while ($row = $result->fetch_assoc()) {
            $teachers = explode(", ", $row['teachers']);
            $formattedNames = array_map(
                function ($name) {
                    return $this->formatter->formatUsername($name);
                },
                $teachers,
            );

            $subject = new SubjectDTO(
                $this->formatter->formatUsername($studentUsername),
                $this->usernameManager->create($studentUsername),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                implode(", ", $formattedNames),
                $this->usernameManager->create($teachers[0]),
                $row['is_interdisciplinary'],
            );

            $subjects->addSubject($subject);
        }

        return $subjects;
    }

    /**
     * A simple helper to transform a request result from the repository into a usable subjects collection.
     * This version is specific to teacher subjects.
     * There are three declinations of this function for students, teachers and admins (all) as the formatting varies
     * from one role to another as the inputs are different.
     * @param mysqli_result $result The result of the request from the repository.
     * @param string $teacherUsername The teacher's username.
     * @return SubjectsCollection The array containing all the subjects in a more usable format.
     * The `SubjectsCollection` is also used here as it implements a custom way to transform it into a JSON array and
     * thus make the output process ot the frontend more straightforward.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    private function formatTeacherSubjects(mysqli_result $result, string $teacherUsername): SubjectsCollection
    {
        $subjects = new SubjectsCollection();

        while ($row = $result->fetch_assoc()) {
            $subject = new SubjectDTO(
                $this->formatter->formatUsername($row['student']),
                $this->usernameManager->create($row['student']),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                $this->formatter->formatUsername($teacherUsername),
                $this->usernameManager->create($teacherUsername),
                $row['is_interdisciplinary'],
                (bool) $row['draftPath'],
            );

            $subjects->addSubject($subject);
        }

        return $subjects;
    }

    /**
     * A simple helper to transform a request result from the repository into a usable subjects collection.
     * This version is specific to admin accounts and thus is made to format all the subjects at, so it doesn't
     * require a username.
     * There are three declinations of this function for students, teachers and admins (all) as the formatting varies
     * from one role to another as the inputs are different.
     * @param mysqli_result $result The result of the request from the repository
     * @return SubjectsCollection The array containing all the subjects in a more usable format.
     * The `SubjectsCollection` is also used here as it implements a custom way to transform it into a JSON array and
     * thus make the output process to the frontend more straightforward.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    private function formatAllSubjects(mysqli_result $result): SubjectsCollection
    {
        $subjects = new SubjectsCollection();

        while ($row = $result->fetch_assoc()) {
            $teachers = explode(", ", $row['teachers']);
            $formattedNames = array_map(
                function ($name) {
                    return $this->formatter->formatUsername($name);
                },
                $teachers,
            );

            $subject = new SubjectDTO(
                $this->formatter->formatUsername($row['student']),
                $this->usernameManager->create($row['student']),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                implode(", ", $formattedNames),
                $this->usernameManager->create($teachers[0]),
                $row['is_interdisciplinary'],
            );

            $subjects->addSubject($subject);
        }

        return $subjects;
    }

    /**
     * Gets all the subjects for a given student.
     * It uses the subject repository to communicate with the database.
     * The subjects are returned using a subject collection object.
     * @param string $studentUsername The student's username.
     * @return SubjectsCollection The array of all the student's subjects.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    public function getStudentSubjects(string $studentUsername): SubjectsCollection
    {

        $result = $this->repo->findByStudent($studentUsername);

        $this->logger->info(
            LoggerInitiator::CORE,
            "Successfully fetched the subjects for student : " . $studentUsername,
        );

        return $this->formatStudentSubjects($result, $studentUsername);
    }

    /**
     * Gets all the subjects for a given teacher.
     * It uses the subject repository to communicate with the database.
     * The subjects are returned using a subject collection object.
     * @param string $teacherUsername The teacher's username.
     * @return SubjectsCollection The array of all the teacher's subjects.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     * */
    public function getTeacherSubjects(string $teacherUsername): SubjectsCollection
    {
        $result = $this->repo->findByTeacher($teacherUsername);

        $this->logger->info(
            LoggerInitiator::CORE,
            "Successfully fetched the subjects for teacher : " . $teacherUsername,
        );

        return $this->formatTeacherSubjects($result, $teacherUsername);
    }

    /**
     * Gets all the subjects thus it should only be used for admins.
     * It uses the subject repository to communicate with the database.
     * The subjects are returned using a subject collection object.
     * @return SubjectsCollection The array of all the subjects inside the database.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    public function getAllSubjects(): SubjectsCollection
    {
        $result = $this->repo->findAll();

        $this->logger->info(
            LoggerInitiator::CORE,
            "Granted access to all subjects for user : " . ($_SESSION[GoralysConfig::SESSION::USERNAME] ?? ""),
        );

        return $this->formatAllSubjects($result);
    }
}
