<?php

namespace Goralys\Core\Support\Data;

use Goralys\Core\Support\Data\Enums\SupportReason;
use JsonSerializable;

/**
 * The DTO used to represent a support ticket.
 */
final readonly class SupportTicketDTO implements JsonSerializable
{
    /**
     * @param int $id The id of the ticket.
     * @param SupportReason $reason The reason of the support ticket.
     * @param string $opener The public id of the user who opened the ticket.
     * @param string $message The message of the ticket.
     */
    public function __construct(
        public int $id,
        public SupportReason $reason,
        public string $opener,
        public string $message,
    ) {}

    /**
     * Transforms the ticket's data into a JSON array that is then sent to the frontend.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "reason" => $this->reason->value,
            "opener" => $this->opener,
            "message" => $this->message,
        ];
    }
}
