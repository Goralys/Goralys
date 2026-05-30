<?php

namespace Goralys\Core\Support\Data;

use DateTime;
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
     * @param string $openerUsername The username of the use who opened the ticket.
     * @param string $opener The public id of the user who opened the ticket.
     * @param string $email The email adress of the user who opened the ticket.
     * @param string $message The message of the ticket.
     */
    public function __construct(
        public int $id,
        public SupportReason $reason,
        public string $openerUsername,
        public string $opener,
        public string $email,
        public string $message,
        public DateTime $createdAt
    ) {
    }

    /**
     * Transforms the ticket's data into a JSON array that is then sent to the frontend.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "reason" => $this->reason->value,
            "openerToken" => $this->opener,
            "opener" => $this->openerUsername,
            "message" => $this->message,
            "createdAt" => $this->createdAt,
        ];
    }
}
