<?php

namespace Goralys\Shared\Config;

/**
 * Global configuration for the app backend.
 */
final readonly class GoralysConfig
{
    public const string SESSION = SessionConfig::class;
    public const string COOKIES = CookiesConfig::class;
    public const string USER = UserConfig::class;
}
