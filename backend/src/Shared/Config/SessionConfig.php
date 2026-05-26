<?php

namespace Goralys\Shared\Config;

/**
 * Global configuration for the app session.
 */
final readonly class SessionConfig
{
    public const string ID = "current_id";
    public const string USERNAME = "current_username";
    public const string FULL_NAME = "current_full_name";
    public const string PUBLIC_ID = "current_public_id";
    public const string ROLE = "current_role";
    public const string EMAIL = "current_email";

    /** @var string[]  */
    public const array USER_CACHE = [
        self::ID,
        self::USERNAME,
        self::FULL_NAME,
        self::PUBLIC_ID,
        self::ROLE,
        self::EMAIL,
    ];
}
