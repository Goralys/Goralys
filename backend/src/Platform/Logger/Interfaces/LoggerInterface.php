<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger\Interfaces;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

/**
 * Contract for the application logger.
 * Covers log rotation and the standard severity levels.
 */
interface LoggerInterface
{
    /**
     * Rotates the log file, archiving or clearing old entries.
     * Should be called once at logger initialization.
     */
    public function rotate(): void;

    /**
     * Logs an informational message.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function info(LoggerInitiator $initiator, string $message): void;

    /**
     * Logs a debug message.
     * Intended for development and diagnostic output, not production.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function debug(LoggerInitiator $initiator, string $message): void;

    /**
     * Logs a warning.
     * Use for recoverable anomalies that do not interrupt execution.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function warning(LoggerInitiator $initiator, string $message): void;

    /**
     * Logs a non-fatal error.
     * Use for failures that are caught and handled but should be investigated.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function error(LoggerInitiator $initiator, string $message): void;

    /**
     * Logs a fatal error.
     * Use for unrecoverable failures that halt or critically compromise execution.
     * @param LoggerInitiator $initiator The component that emitted the log entry.
     * @param string $message The message to log.
     */
    public function fatal(LoggerInitiator $initiator, string $message): void;

    /**
     * Returns all the logs stored by the logger.
     * @return array The list of all logs registered by the logger.
     */
    public function store(): array;
}
