<?php

namespace Goralys\Core\Audit\Repository\Interfaces;

use Goralys\Core\Audit\Data\AuditAction;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use mysqli_result;

/**
 * Contract for the audit action repositories - which can be used to retrieve audit actions from the database.
 */
interface AuditRepositoryInterface
{
    public function __construct(LoggerInterface $logger, DbContainerInterface $db);

    /**
     * This method inserts a new action inside the database.
     * @param AuditAction $action The action to insert.
     * @return bool Wether the insertion was successful.
     */
    public function insert(AuditAction $action): bool;

    /**
     * Retrieves all the actions triggered by a given admin from the database.
     * @param string $username The username of the admin to retrieve the actions for.
     * @param int $max The maximum number of actions to query, a limit of 0 will cause the method to retrieve all
     * actions.
     * @return mysqli_result The raw query result.
     */
    public function getForInitiator(string $username, int $max): mysqli_result;
}
