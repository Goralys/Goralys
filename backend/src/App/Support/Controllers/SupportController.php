<?php

namespace Goralys\App\Support\Controllers;

use Goralys\Core\Subjects\Repository\SubjectsRepository;
use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\Support\Data\SupportTicketDTO;
use Goralys\Core\Support\Data\SupportTicketsCollection;
use Goralys\Core\Support\Repository\SupportRepository;
use Goralys\Platform\DB\Facade\DbContainer;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;

final readonly class SupportController
{
    private LoggerInterface $logger;
    private SupportRepository $repo;

    public function __construct(LoggerInterface $logger, DbContainer $db)
    {
        $this->logger = $logger;
        $this->repo = new SupportRepository($this->logger, $db);
    }

    public function getTickets(): SupportTicketsCollection
    {
        return $this->repo->getAllTickets();
    }

    public function getTicket(int $id): ?SupportTicketDTO
    {
        try {
            return $this->repo->getTicket($id);
        } catch (GoralysRuntimeException) {
            return null;
        }
    }

    public function createTicket(SupportReason $reason, string $message): ?SupportTicketDTO
    {
        return $this->repo->createTicket($reason, $_SESSION[GoralysConfig::SESSION::USERNAME], $message);
    }
}
