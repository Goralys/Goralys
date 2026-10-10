<?php

namespace Goralys\Core\Audit\Data;

use DateTime;
use Goralys\Core\Audit\Data\Enums\AuditActionType;
use JsonSerializable;

/**
 * This DTO represents an audit actions, such actions are always triggered by admins.
 * They are then stored inside a dedicated table inside the database.
 */
final readonly class AuditAction implements JsonSerializable
{
    /**
     * @param ?int $id The unique id of the action.
     * @param string $initiator The username of the admin who triggered the action.
     * @param AuditActionType $type The type of the action.
     * @param string $description The extended description of the action.
     * @param ?DateTime $at The precise date of the action.
     */
    public function __construct(
        public ?int $id = null,
        public string $initiator,
        public AuditActionType $type,
        public string $description,
        public ?DateTime $at = null,
    ) {
    }

    /**
     * This converts an audit action into a simple JSON array that can then be sent to the frontend.
     * @return array The comprehensive array containing all information about the action.
     */
    public function jsonSerialize(): array
    {
        return [
            "initiator" => $this->initiator,
            "type" => $this->type->value,
            "description" => $this->description,
            "at" => $this->at,
        ];
    }
}
