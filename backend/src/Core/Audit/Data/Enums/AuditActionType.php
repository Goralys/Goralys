<?php

namespace Goralys\Core\Audit\Data\Enums;

/**
 * This enum represents the different supported types of audit actions.
 * It also includes a generic type ("other"), but it should only be used when no other type fits the action.
 */
enum AuditActionType: string
{
    case ADD = "add";
    case UPDATE = "update";
    case DELETE = "delete";
    case OTHER = "other";
}
