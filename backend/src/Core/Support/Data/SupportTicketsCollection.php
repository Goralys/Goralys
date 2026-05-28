<?php

namespace Goralys\Core\Support\Data;

use JsonSerializable;

class SupportTicketsCollection implements JsonSerializable
{
    /* @var SupportTicketDTO[] */
    public array $tickets = [] {
        get {
            return $this->tickets;
        }
    }

    /**
     * Adds a new ticket to the collection.
     * @param SupportTicketDTO $newTicket The ticket to add.
     * @return void
     */
    public function addTicket(SupportTicketDTO $newTicket): void
    {
        $this->tickets = [...$this->tickets, $newTicket];
    }

    /**
     * Transforms the ticket collection into a JSON array
     * @return SupportTicketDTO[]
     */
    public function jsonSerialize(): array
    {
        return $this->tickets;
    }
}
