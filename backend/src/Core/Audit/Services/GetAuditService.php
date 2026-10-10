<?php

namespace Goralys\Core\Audit\Services;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\Audit\Data\AuditAction;
use Goralys\Core\Audit\Data\AuditActionsCollection;
use Goralys\Core\Audit\Data\Enums\AuditActionType;
use Goralys\Core\Audit\Repository\Interfaces\AuditRepositoryInterface;

/**
 * This service is used to retrieve audit actions from the database using a dedicated repository.
 */
final class GetAuditService
{
    private AuditRepositoryInterface $repo;

    /**
     * @param AuditRepositoryInterface $repo The injected repository.
     */
    public function __construct(AuditRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Returns all audit actions for a given admin.
     * @param string $initiator The username of the admin to get the actions for.
     * @param int $max The maximum number of actions to return.
     * If the maximum is 0, the function will return all audit actions for this admin.
     * @return AuditActionsCollection The list of all audit actions for the given admin.
     * @throws DateMalformedStringException If the timestamp of the action fails to create a valid {@see DateTime}
     * object.
     */
    public function getFor(string $initiator, int $max): AuditActionsCollection
    {
        $result = $this->repo->getForInitiator($initiator, $max);
        $c = new AuditActionsCollection();

        while ($row = $result->fetch_assoc()) {
            $c->addAction(new AuditAction(
                $row['id'],
                $row['initiator'],
                AuditActionType::from($row['action_type']),
                $row['action_description'],
                new DateTime($row['action_at'])
            ));
        }

        return $c;
    }
}
