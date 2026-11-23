<?php

namespace Goralys\App\Cookies;

use Goralys\App\Config\CookiesConfig;

final class Cookies
{
    public static function set(string $name, string $value, int $ttl): bool
    {
        return setcookie($name, $value, [
                'expires'  => time() + $ttl,
                ...CookiesConfig::params()
        ]);
    }
}
