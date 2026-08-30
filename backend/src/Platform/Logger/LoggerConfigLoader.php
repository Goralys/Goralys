<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\Logger;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

/**
 * A small loader to load the necessary configuration for the logger.
 */
final class LoggerConfigLoader
{
    private static array $loggerFiles;
    /* @var array<string, int> */
    private static array $filesLifeTime = [
        "APP" => 7,
        "CORE" => 14,
        "PLATFORM" => 20,
        "KERNEL" => 14,
        "GLOBAL" => 30,
    ];
    private static string $goralysEnv;
    private static string $baseDir = __DIR__ . "/../../../Logs/";
    private static bool $isInitialized = false;

    /**
     * Initializes the respective log files for the different layers and for all instances of the loader (static array).
     * @return void
     */
    final public static function init(): void
    {
        if (!self::$isInitialized) {
            self::$loggerFiles = [
                "APP" => $_ENV["LOGGER_APP_FILENAME"] ?? "Goralys_App",
                "CORE" => $_ENV["LOGGER_CORE_FILENAME"] ?? "Goralys_Core",
                "PLATFORM" => $_ENV["LOGGER_PLATFORM_FILENAME"] ?? "Goralys_Platform",
                "KERNEL" => $_ENV["LOGGER_KERNEL_FILENAME"] ?? "Goralys_Kernel",
                "GLOBAL" => $_ENV["LOGGER_GLOBAL_FILENAME"] ?? "Goralys_Global",
            ];
            self::$goralysEnv = $_ENV['GORALYS_ENVIRONMENT'] ?? "testing"; // fallback when no env is loaded
            self::$isInitialized = true;
        }
    }

    /**
     * Returns the name of the log file for the given layer.
     * Note that the full path is constructed inside the `LoggerService`.
     * @param LoggerInitiator $type The layer to get the log file for.
     * @return string The name of the file.
     */
    final public static function getInitiatorFile(LoggerInitiator $type): string
    {
        return self::$loggerFiles[$type->name];
    }

    /**
     * Get the global log file
     * Note that the full path is constructed inside the `LoggerService`.
     * @return string The name of the file
     */
    final public static function getGlobalFile(): string
    {
        return self::$loggerFiles["GLOBAL"];
    }

    /**
     * Returns the full path to the log file for the given layer.
     * @param LoggerInitiator $type The layer to get the path to the log file for.
     * @return string The path to the file.
     */
    final public static function getInitiatorPath(LoggerInitiator $type): string
    {
        return self::$baseDir . self::$loggerFiles[$type->name] . ".log";
    }

    /**
     * Get the full path to the global log.
     * @return string The path to the file.
     */
    final public static function getGlobalPath(): string
    {
        return self::$baseDir . self::$loggerFiles["GLOBAL"] . ".log";
    }

    /**
     * Get the current environment.
     * The two possible values are 'dev' and 'prod'.
     * @return string The current environment.
     */
    public static function getGoralysEnv(): string
    {
        return self::$goralysEnv;
    }

    /**
     * Gets the lifetime of a log file.
     * @param LoggerInitiator $type The layer to get the log file for.
     * @return int The lifetime of the file in seconds.
     */
    public static function getFileLifeTime(LoggerInitiator $type): int
    {
        return self::$filesLifeTime[$type->name] * 24 * 60 * 60;
    }

    /**
     * Get the global log file lifetime.
     * @return int The lifetime of the file.
     */
    final public static function getGlobalLifetime(): int
    {
        return self::$filesLifeTime["GLOBAL"] * 24 * 60 * 60;
    }

    /**
     * Gets the root logs directory
     * @return string
     */
    public static function getBaseDir(): string
    {
        return self::$baseDir;
    }
}
