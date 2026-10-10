<?php

namespace Goralys\Core\Audit\Repository;

use Goralys\Core\Audit\Data\AuditAction;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use mysqli_result;

class AuditRepository implements Interfaces\AuditRepositoryInterface
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     */
    public function __construct(LoggerInterface $logger, DbContainerInterface $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Inserts a new action inside the database.
     * @param AuditAction $action The action to insert.
     * @return bool Wether the insertion was successful.
     */
    public function insert(AuditAction $action): bool
    {
        return $this->db->run(
            "insert into audit (initiator, initiator_snapshot, action_type, action_description)
            values (?, ?, ?, ?)",
            "ssss",
            $action->initiator,
            $action->initiator,
            $action->type->value,
            $action->description
        );
    }

    /**
     * Retrieves all the actions triggered by a given admin from the database.
     * @param string $username The username of the admin to retrieve the actions for.
     * @param int $max The maximum number of actions to query, a limit of 0 will cause the method to retrieve all
     * actions.
     * @return mysqli_result The raw query result.
     */
    public function getForInitiator(string $username, int $max): mysqli_result
    {
        return $this->db->fetch(
            "select id, initiator, action_type, action_description, action_at
            from audit
            where initiator = ?
            limit ?",
            "si",
            $username,
            $max
        );
    }
}
