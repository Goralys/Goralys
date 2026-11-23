<?php

namespace Goralys\App\Config;

final class CookiesConfig
{
    private static array $PARAMS;

    /**
     * This function is used to configure and get the parameters used by the cookie config.
     * @param ?array $params If provide and not null, this will be set as the new domain to use. If it is null, then the
     * params are not altered and the current ones are returned.
     * @return ($params is null ? array : null) The current parameters if no params were provided, null otherwhise.
     */
    public static function params(?array $params = null): ?array
    {
        if ($params) {
            self::$PARAMS = $params;
        } else {
            return self::$PARAMS;
        }

        return null;
    }
}
