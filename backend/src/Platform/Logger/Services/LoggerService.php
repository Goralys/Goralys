<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger\Services;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Data\Enums\LoggerType;
use Goralys\Platform\Logger\LoggerConfigLoader;

/**
 * Main logging service.
 * It provides a global `log` method to log to the correct file.
 */
final class LoggerService
{
    private static string $logDirectory;

    /**
     * Initializes the log path for all instances of the service.
     * @param string $logDirectory The path to the "Logs" directory (you can name it as you want).
     * @return void
     */
    final public static function init(
        string $logDirectory,
    ): void {
        LoggerService::$logDirectory = $logDirectory;
    }

    /**
     * Functions used to log a message
     * @param LoggerInitiator $initiator The initiator (layer) of the log.
     * @param LoggerType $type The type of the log, there are currently five types:
     * - debug
     * - info
     * - warning
     * - error
     * - fatal
     * @param string $message The message to log.
     * @param ?array $logStore A special array that contains all the logs. The new log will be appended to this array
     * using the following format: ["initiator" => <initiator>, "level" => <type>, "message" => <message>,
     * "timestamp" => <now>]. All fields in the array are strings.
     * NOTE: this array is currently only used for testing but is also useful for future audit features.
     * @return void
     */
    final public static function log(
        LoggerInitiator $initiator,
        LoggerType $type,
        string $message,
        ?array &$logStore
    ): void {
        if ($type === LoggerType::Debug && LoggerConfigLoader::getGoralysEnv() === 'prod') {
            return;
        }

        if (!is_dir(LoggerService::$logDirectory)) {
            mkdir(LoggerService::$logDirectory, 0755, true);
        }

        $filename = LoggerConfigLoader::getInitiatorFile($initiator);
        $time = date("Y-m-d H:i:s");

        $logStore[] = [
            "initiator" => $initiator->toString(),
            "level" => $type->toString(),
            "message" => $message,
            "timestamp" => $time
        ];

        // Logs to the layer-specific log file
        if ($file = fopen(LoggerService::$logDirectory . $filename . ".log", "a")) {
            self::writeLog($file, $initiator, $type, $time, $message);
        }

        // Logs to the global log file
        if ($file = fopen(LoggerService::$logDirectory . LoggerConfigLoader::getGlobalFile() . ".log", "a")) {
            self::writeLog($file, $initiator, $type, $time, $message);
        }
    }

    /**
     * Writes a log entry to the specified file.
     * @param mixed $file The file to write the log into.
     * @param LoggerInitiator $initiator The initiator of the log.
     * @param LoggerType $type The log type.
     * @param string $time The time of the log.
     * @param string $message The log's message.
     * @return void
     */
    private static function writeLog(
        mixed $file,
        LoggerInitiator $initiator,
        LoggerType $type,
        string $time,
        string $message,
    ): void {
        flock($file, LOCK_EX);

        fwrite(
            $file,
            "($initiator->value)[$type->name:" . self::getCallingClass() . "]{session:" . (session_id() ?? "none")
            . "} at $time : "
            . "$message" . PHP_EOL,
        );

        fflush($file);
        flock($file, LOCK_UN);
        fclose($file);
    }

    /**
     * Gets the class name that called the logger.
     * @return string The calling class name, or 'Unknown' if unable to determine.
     */
    private static function getCallingClass(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        // Trace[0] = getCallingClass()
        // Trace[1] = write_log()
        // Trace[2] = log() [from the logger]
        // Trace[3] = info/debug/warning/error/fatal()
        // Trace[4] = actual caller
        if (!isset($trace[4]['class'])) {
            return 'Unknown';
        }
        if (!$class = $trace[4]['class']) {
            return 'Unknown';
        }
        $parts = explode("\\", $class);
        return $parts[count($parts) - 1];
    }
}
