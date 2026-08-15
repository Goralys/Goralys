<?php

namespace Goralys\Shared\Config;

final readonly class AuthConfig
{
    public const int AUTH_TOKEN_LENGTH = 32;
    public const int AUTH_TOKEN_LIFETIME = 30; // days
    public const string AUTH_TOKEN_ALGORITHM = 'sha256';
}
