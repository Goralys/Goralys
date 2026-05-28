<?php

namespace Goralys\Core\Support\Repository\Interfaces;

use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\Support\Data\SupportTicketDTO;
use Goralys\Core\Support\Data\SupportTicketsCollection;

interface SupportRepositoryInterface
{
    public function createTicket(SupportReason $reason, string $opener, string $message): ?SupportTicketDTO;
    public function getAllTickets(): SupportTicketsCollection;
    public function getTicket(int $id): SupportTicketDTO;
}
