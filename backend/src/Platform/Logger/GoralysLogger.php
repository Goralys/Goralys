<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Data\Enums\LoggerType;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Logger\Services\LoggerService;

/**
 * The main logger class.
 * Provides four functions to log with different verbosity levels.
 */
final class GoralysLogger implements LoggerInterface
{
    private array $logs = [];

    /**
     * Initializes the logger and its sub-services.
     * The backend/Logs directory must exist, else the logger will fail silently,
     * create it manually or use the setup script.
     */
    public function __construct()
    {
        LoggerConfigLoader::init();
        LoggerService::init(LoggerConfigLoader::getBaseDir());
    }

    /**
     * Automatically deletes log files when they get too old
     * @return void
     */
    public function rotate(): void
    {
        foreach (LoggerInitiator::cases() as $case) {
            $lifetime = LoggerConfigLoader::getFileLifeTime($case);
            $path = LoggerConfigLoader::getInitiatorPath($case);

            if (!file_exists($path)) {
                continue;
            }

            $fileAge = time() - filemtime($path);

            if ($fileAge >= $lifetime) {
                unlink($path);

                $dir = dirname($path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0o755, true);
                }

                touch($path);
                chmod($path, 0o644);
            }
        }

        $lifetime = LoggerConfigLoader::getGlobalLifetime();
        $path = LoggerConfigLoader::getGlobalPath();

        if (!file_exists($path)) {
            return;
        }

        $fileAge = time() - filemtime($path);

        if ($fileAge >= $lifetime) {
            unlink($path);

            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0o755, true);
            }

            touch($path);
            chmod($path, 0o644);
        }
    }

    /**
     * Logs with a verbosity level of info.
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param string $message The message to log.
     * @return void
     */
    public function info(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log(
            $initiator,
            LoggerType::Info,
            $message,
            $this->logs
        );
    }

    /**
     * Logs with a verbosity level of debug.
     * This function can only work if the goralys environment is set to 'dev'.
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param string $message The message to log.
     * @return void
     */
    public function debug(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log(
            $initiator,
            LoggerType::Debug,
            $message,
            $this->logs
        );
    }

    /**
     * Logs with a verbosity level of warning.
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param string $message The message to log.
     * @return void
     */
    public function warning(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log(
            $initiator,
            LoggerType::Warning,
            $message,
            $this->logs
        );
    }

    /**
     * Logs with a verbosity level of error.
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param string $message The message to log.
     * @return void
     */
    public function error(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log(
            $initiator,
            LoggerType::Error,
            $message,
            $this->logs
        );
    }

    /**
     * Logs with a verbosity level of fatal.
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param string $message The message to log.
     * @return void
     */
    public function fatal(LoggerInitiator $initiator, string $message): void
    {
        LoggerService::log(
            $initiator,
            LoggerType::Fatal,
            $message,
            $this->logs
        );
    }

    /**
     * Returns all the logs stored by the logger.
     * @return array The list of all logs registered by the logger.
     */
    public function store(): array
    {
        return $this->logs;
    }
}
