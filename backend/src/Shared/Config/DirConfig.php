<?php

namespace Goralys\Shared\Config;

/**
 * Global configuration for the app's directories.
 */
class DirConfig
{
    private const string ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . '..' . DIRECTORY_SEPARATOR;
    public const string ASSETS = self::ROOT . "Assets" . DIRECTORY_SEPARATOR;
    public const string LOGS = self::ROOT . "Logs" . DIRECTORY_SEPARATOR;
    public const string RATE_LIMITER = self::ROOT . "RateLimiter" . DIRECTORY_SEPARATOR;
}
