<?php

namespace Goralys\App\Audit\Controllers;

use DateMalformedStringException;
use Goralys\Core\Audit\Data\AuditActionsCollection;
use Goralys\Core\Audit\Repository\AuditRepository;
use Goralys\Core\Audit\Repository\Interfaces\AuditRepositoryInterface;
use Goralys\Core\Audit\Services\GetAuditService;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * This controller orchestrates the creation and retrieval of audit actions.
 */
final class AuditController
{
    private AuditRepositoryInterface $repo;
    private GetAuditService $getService;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     */
    public function __construct(LoggerInterface $logger, DbContainerInterface $db)
    {
        $this->repo = new AuditRepository($logger, $db);
        $this->getService = new GetAuditService($this->repo);
    }

    /**
     * Inserts all audit actions from a collection.
     * @param AuditActionsCollection $collection The collection to insert.
     * @return bool If all inserts were successful.
     * @throws GoralysRuntimeException If an insertion fails.
     */
    public function insertCollection(AuditActionsCollection $collection): bool
    {
        foreach ($collection->actions as $action) {
            if (!$this->repo->insert($action)) {
                throw new GoralysRuntimeException("Failed to insert a new audit action.");
            }
        }

        return true;
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
    public function get(string $initiator, int $max = 0): AuditActionsCollection
    {
        return $this->getService->getFor($initiator, $max);
    }
}
