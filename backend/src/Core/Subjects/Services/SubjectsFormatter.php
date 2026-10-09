<?php

namespace Goralys\Core\Subjects\Services;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Data\SubjectDTO;
use Goralys\Core\Subjects\Data\SubjectsCollection;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use Goralys\Shared\Exception\GoralysRuntimeException;
use mysqli_result;

final class SubjectsFormatter
{
    private UsernameFormatterService $formatter;
    private UsernameManager $usernameManager;

    /**
     * Initializes the logger, database container and a utility service - the username formatter - used by the service.
     * @param UsernameFormatterService $formatter The injected username formatter.
     * This formatter is used to transform the usernames stored inside the database with the format f.lastnameX to
     * LASTNAME. F with f the first letter of the first name.
     * This is useful to avoid displaying private usernames to other users.
     * Therefore, as the username format is very specific to the project, you may need to tweak the username formatter
     * or get rid of it completely depending on your project.
     */
    public function __construct(
        UsernameFormatterService $formatter,
        UsernameManager $usernameManager,
    ) {
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
    public function student(mysqli_result $result, string $studentUsername): SubjectsCollection
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
                $this->usernameManager->resolve($studentUsername),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                implode(", ", $formattedNames),
                $this->usernameManager->resolve($teachers[0]),
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
    public function teacher(mysqli_result $result, string $teacherUsername): SubjectsCollection
    {
        $subjects = new SubjectsCollection();

        while ($row = $result->fetch_assoc()) {
            $subject = new SubjectDTO(
                $this->formatter->formatUsername($row['student']),
                $this->usernameManager->resolve($row['student']),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                $this->formatter->formatUsername($teacherUsername),
                $this->usernameManager->resolve($teacherUsername),
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
    public function all(mysqli_result $result): SubjectsCollection
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
                $this->usernameManager->resolve($row['student']),
                $row['subject'] ?? "",
                SubjectStatus::from($row['subject_status'] ?? 0),
                $row['comment'] ?? "",
                $row['last_rejected'] ?? "",
                $row['last_updated_at'] ? new DateTime($row['last_updated_at']) : null,
                $row['topic'],
                $row['topic_code'] ?? "",
                implode(", ", $formattedNames),
                $this->usernameManager->resolve($teachers[0]),
                $row['is_interdisciplinary'],
            );

            $subjects->addSubject($subject);
        }

        return $subjects;
    }
}
