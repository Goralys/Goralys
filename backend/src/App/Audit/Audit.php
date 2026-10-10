<?php

namespace Goralys\App\Audit;

use DateTime;
use Goralys\Core\Audit\Data\AuditAction;
use Goralys\Core\Audit\Data\Enums\AuditActionType;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Error\GoralysLogicError;

/**
 * This class is used to statically register audit actions and insert them once the request terminates.
 */
final class Audit
{
    /** @var AuditAction[] */
    private(set) static array $registeredActions = [];

    /**
     * Registers a new audit action that will be inserted when the request terminates.
     * @param AuditActionType $type The type of the action.
     * @param string $description The extended description of the action.
     * It is useful to include exactly what was afected by the action and how it was afected, whille keeping the
     * description short and concise, e.g.: "Deleted user j.doe1".
     * @param ?string $username The username of the admin who trigered the action. If no username is specified,
     * the function uses the currently logged in user's one instead.
     * @return void
     */
    public static function registerAction(AuditActionType $type, string $description, ?string $username = null): void
    {
        $username = $username ?? $_SESSION[Config::SESSION::USERNAME];

        if (!$username) {
            throw new GoralysLogicError("Cannot create audit action with empty username");
        }

        self::$registeredActions[] = new AuditAction(null, $username, $type, $description, new DateTime());
    }
}
