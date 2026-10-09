<?php

namespace Goralys\Core\Subjects\Services;

use DateMalformedStringException;
use Goralys\Core\Subjects\Data\SubjectsCollection;
use Goralys\Core\Subjects\Data\SubjectsFilter;
use Goralys\Core\Subjects\Data\SubjectsFilterOptions;
use Goralys\Core\Subjects\Repository\Interfaces\SubjectsRepositoryInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * This servie is used to apply and resolve filters for subjects
 */
final class SubjectsFilterer
{
    private SubjectsRepositoryInterface $repo;
    private SubjectsFormatter $formatter;

    /**
     * @param SubjectsRepositoryInterface $repo The injected repository.
     * @param SubjectsFormatter $formatter The injected subjects formatter.
     */
    public function __construct(SubjectsRepositoryInterface $repo, SubjectsFormatter $formatter)
    {
        $this->repo = $repo;
        $this->formatter = $formatter;
    }

    /**
     * Returns all subjects belonging to students with at least one subject matching the filter.
     * @param SubjectsFilter $filter The filter to apply to the subjects.
     * @return SubjectsCollection The list of all matching subjects.
     * @throws DateMalformedStringException If one the date column fails to create a valid {@see DateTime} object.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    public function query(SubjectsFilter $filter): SubjectsCollection
    {
        return $this->formatter->all($this->repo->findFiltered($filter));
    }

    /**
     * Returns the options available when filtering subjects.
     * The subjects statuses are omitted because they are already a constant closed set.
     * @return SubjectsFilterOptions The filetring options.
     */
    public function opt(): SubjectsFilterOptions
    {
        $resultC = $this->repo->getClassrooms();
        $resultT = $this->repo->getTopicGroups();
        $c = $t = []; // classrooms and topics list init.

        while ($row = $resultC->fetch_assoc()) {
            $c[$row['classroom']][] = $row['student'];
        }
        while ($row = $resultT->fetch_assoc()) {
            $t[$row['topic']][] = $row['student'];
        }

        return new SubjectsFilterOptions($c, $t);
    }
}
