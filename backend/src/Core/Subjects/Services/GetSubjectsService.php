<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Subjects\Services;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\Subjects\Data\SubjectsCollection;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * The service used to fetch subjects inside the database via the subject repository
 */
final class GetSubjectsService
{
    private LoggerInterface $logger;
    private SubjectsRepositoryInterface $repo;
    private SubjectsFormatter $formatter;
    /**
     * Initializes the logger, database container and a utility service - the username formatter - used by the service.
     * @param LoggerInterface $logger The injected logger
     * @param SubjectsRepositoryInterface $repo The injected subjects repository
     * @param SubjectsFormatter $formatter The injected subjects formatter.
     * This formatter is used to transform the raw SQL results into {@see SubjectsCollection}
     */
    public function __construct(
        LoggerInterface $logger,
        SubjectsRepositoryInterface $repo,
        SubjectsFormatter $formatter,
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->formatter = $formatter;
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

        return $this->formatter->student($result, $studentUsername);
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

        return $this->formatter->teacher($result, $teacherUsername);
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

        return $this->formatter->all($result);
    }
}
