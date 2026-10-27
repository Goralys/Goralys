<?php

namespace Goralys\Platform\Logger;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Data\Enums\LoggerType;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Logger\Services\LoggerService;

final class PanicLogger implements LoggerInterface
{
    public function rotate(): void
    {
    }

    /**
     * Logs an informational message.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function info(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log($initiator, LoggerType::Info, $message, panic: true);
    }

    /**
     * Logs a debug message.
     * Intended for development and diagnostic output, not production.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function debug(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log($initiator, LoggerType::Debug, $message, panic: true);
    }

    /**
     * Logs a warning.
     * Use for recoverable anomalies that do not interrupt execution.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function warning(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log($initiator, LoggerType::Warning, $message, panic: true);
    }

    /**
     * Logs a non-fatal error.
     * Use for failures that are caught and handled but should be investigated.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function error(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log($initiator, LoggerType::Error, $message, panic: true);
    }

    /**
     * Logs a fatal error.
     * Use for unrecoverable failures that halt or critically compromise execution.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function fatal(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log($initiator, LoggerType::Fatal, $message, panic: true);
    }
}
