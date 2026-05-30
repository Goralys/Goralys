<?php

namespace Goralys\Core\Support\Repository;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\Support\Data\SupportTicketDTO;
use Goralys\Core\Support\Data\SupportTicketsCollection;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;
use mysqli_result;

final class SupportRepository implements Interfaces\SupportRepositoryInterface
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;
    private UsernameManager $usernames;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     * @param UsernameManager $usernames The injected username manager.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
        UsernameManager $usernames
    ) {
        $this->logger = $logger;
        $this->db = $db;
        $this->usernames = $usernames;
    }

    /**
     * @throws GoralysRuntimeException|DateMalformedStringException
     */
    private function buildTicketFromResult(mysqli_result $result): SupportTicketDTO
    {
        if ($result->num_rows === 0) {
            $this->logger->warning(LoggerInitiator::CORE, "Expected at least one ticket, found 0.");
            throw new GoralysRuntimeException("Failed to retrieve ticket.");
        }

        $row = $result->fetch_assoc();
        return new SupportTicketDTO(
            $row['id'],
            SupportReason::fromString($row['reason']),
            UsernameFormatterService::formatUsername($row['opener']),
            $this->usernames->create($row['opener']),
            $row['email'],
            $row['message'],
            new DateTime($row['created_at'])
        );
    }

    private function buildTicketsFromResult(mysqli_result $result): SupportTicketsCollection
    {
        $collection = new SupportTicketsCollection();
        while ($row = $result->fetch_assoc()) {
            try {
                $collection->addTicket(
                    new SupportTicketDTO(
                        $row['id'],
                        SupportReason::fromString($row['reason']),
                        UsernameFormatterService::formatUsername($row['opener']),
                        $this->usernames->create($row['opener']),
                        $row['email'],
                        $row['message'],
                        new DateTime($row['created_at'])
                    ),
                );
            } catch (GoralysRuntimeException | DateMalformedStringException) {
                continue;
            }
        }

        return $collection;
    }

    public function getAllTickets(): SupportTicketsCollection
    {
        $result = $this->db->fetchNoArgs(
            "SELECT id, opener, message, reason, email, created_at from tickets",
        );

        return $this->buildTicketsFromResult($result);
    }

    /**
     * @throws GoralysRuntimeException|DateMalformedStringException
     */
    public function getTicket(int $id): SupportTicketDTO
    {
        $result = $this->db->fetch(
            "SELECT id, opener, message, reason, email, created_at from tickets where id = ?",
            "i",
            $id,
        );

        return $this->buildTicketFromResult($result);
    }

    /**
     * Creates a new support ticket.
     * @param SupportReason $reason The reason of the support ticket.
     * @param string $opener The public id of the user opening the ticket.
     * @param string $email The email of the use who opened the support ticket.
     * @param string $message The message of the support ticket.
     * @return ?SupportTicketDTO The id of the new ticket, or null if the insertion fails.
     */
    public function createTicket(SupportReason $reason, string $opener, string $email, string $message): ?int
    {
        $result = $this->db->fetch(
            "insert into tickets (reason, opener, message, email) values (?, ?, ?, ?) returning id",
            "ssss",
            $reason->value,
            $opener,
            $message,
            $email
        );

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc()['id'];
    }

    public function deleteTicket(int $id): bool
    {
        return $this->db->run(
            "delete from tickets where id = ?",
            "i",
            $id
        );
    }
}
