<?php

namespace Goralys\App\Support\Controllers;

use DateMalformedStringException;
use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\Support\Data\SupportTicketDTO;
use Goralys\Core\Support\Data\SupportTicketsCollection;
use Goralys\Core\Support\Repository\SupportRepository;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Platform\DB\Facade\DbContainer;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Mail\Config\MailerConfig;
use Goralys\Platform\Mail\Interfaces\MailContainerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;

final readonly class SupportController
{
    private LoggerInterface $logger;
    private SupportRepository $repo;
    private UsernameManager $usernames;

    public function __construct(LoggerInterface $logger, DbContainer $db, UsernameManager $usernames)
    {
        $this->logger = $logger;
        $this->usernames = $usernames;
        $this->repo = new SupportRepository($this->logger, $db, $this->usernames);
    }

    public function getTickets(): SupportTicketsCollection
    {
        return $this->repo->getAllTickets();
    }

    public function createTicket(SupportReason $reason, string $email, string $message): ?int
    {
        return $this->repo->createTicket($reason, $_SESSION[GoralysConfig::SESSION::USERNAME], $email, $message);
    }

    public function resolveTicket(int $id, string $message, MailContainerInterface $mailer): bool
    {
        try {
            $ticket = $this->repo->getTicket($id);
        } catch (GoralysRuntimeException | DateMalformedStringException) {
            return false;
        }

        if (!$this->repo->deleteTicket($id)) {
            return false;
        }

        $mailer->sendMail(
            MailerConfig::BASE_ALIAS,
            "Ticket #" . $ticket->id . "[" . SupportReason::getDisplay($ticket->reason) . "]",
            "Bonjour, <br>
                    Nous vous informons que votre problème a bien été résolu par notre équipe de support. <br><br>
                    Message du support: <br>"
                    . nl2br($message)
                    . "<br>Nous vous souhaitons une agréable journée sur Goralys.",
            $ticket->email
        );
        return true;
    }

    public function getTicket(int $id): ?SupportTicketDTO
    {
        try {
            return $this->repo->getTicket($id);
        } catch (GoralysRuntimeException) {
            return null;
        }
    }
}
