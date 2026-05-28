<?php

namespace Goralys\Core\Support\Repository;

use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\Support\Data\SupportTicketDTO;
use Goralys\Core\Support\Data\SupportTicketsCollection;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\GoralysRuntimeException;

final class SupportRepository implements Interfaces\SupportRepositoryInterface
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
    ) {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * @throws GoralysRuntimeException
     */
    private function buildTicketFromResult(\mysqli_result $result): SupportTicketDTO
    {
        if ($result->num_rows === 0) {
            $this->logger->warning(LoggerInitiator::CORE, "Expected at least one ticket, found 0.");
            throw new GoralysRuntimeException("Failed to retrieve ticket.");
        }

        $row = $result->fetch_assoc();
        return new SupportTicketDTO(
            $row['id'],
            SupportReason::fromString($row['reason']),
            $row['opener'],
            $row['message'],
        );
    }

    private function buildTicketsFromResult(\mysqli_result $result): SupportTicketsCollection
    {
        $collection = new SupportTicketsCollection();
        while ($row = $result->fetch_assoc()) {
            $collection->addTicket(
                new SupportTicketDTO(
                    $row['id'],
                    SupportReason::fromString($row['reason']),
                    $row['opener'],
                    $row['message'],
                ),
            );
        }

        return $collection;
    }

    public function getAllTickets(): SupportTicketsCollection
    {
        $result = $this->db->fetchNoArgs(
            "SELECT id, opener, message from tickets",
        );

        return $this->buildTicketsFromResult($result);
    }

    /**
     * @throws GoralysRuntimeException
     */
    public function getTicket(int $id): SupportTicketDTO
    {
        $result = $this->db->fetch(
            "SELECT id, opener, message from tickets where id = ?",
            "i",
            $id,
        );

        return $this->buildTicketFromResult($result);
    }

    /**
     * Creates a new support ticket.
     * @param SupportReason $reason The reason of the support ticket.
     * @param string $opener The public id of the user opening the ticket.
     * @param string $message The message of the support ticket.
     * @return ?SupportTicketDTO The id of the new ticket, or null if the insertion fails.
     */
    public function createTicket(SupportReason $reason, string $opener, string $message): ?SupportTicketDTO
    {
        $result = $this->db->fetch(
            "insert into tickets (reason, opener, message) values (?, ?, ?) returning id",
            "sss",
            $reason->value,
            $opener,
            $message,
        );

        if ($result->num_rows === 0) {
            return null;
        }

        $id = $result->fetch_assoc()['id'];

        return new SupportTicketDTO(
            $id,
            $reason,
            $opener,
            $message,
        );
    }
}
