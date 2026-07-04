<?php

namespace Goralys\App\Router\Options;

final class ToastOptions extends Option
{
    public const string MAIN_KEY = 'toast';
    public const string FLASH_KEY = 'is-flash';

    /** Marks a request as using flash toasts.
     * @return array[] The option array to pass to the option builder.
     */
    public static function flash(): array
    {
        return [[self::MAIN_KEY => [self::FLASH_KEY => "true"]]];
    }
}
