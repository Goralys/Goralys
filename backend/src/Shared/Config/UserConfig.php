<?php

namespace Goralys\Shared\Config;

final readonly class UserConfig
{
    public const string ADMIN_SUFFIX = ".admin";

    // backend/Users
    public const string BASE_DIR = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".."
    . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Users" . DIRECTORY_SEPARATOR;
    public const string USERNAME_LIST_PATH = self::BASE_DIR . "usernames.txt";
}
