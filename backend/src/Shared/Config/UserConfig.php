<?php

namespace Goralys\Shared\Config;

final readonly class UserConfig
{
    public const string ADMIN_SUFFIX = ".admin";

    // backend/Assets/Users
    public const string BASE_DIR = DirConfig::ASSETS . "Users" . DIRECTORY_SEPARATOR;
    public const string USERNAME_LIST_PATH = self::BASE_DIR . "usernames.txt";
}
