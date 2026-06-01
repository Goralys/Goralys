<?php

namespace Goralys\Platform\Mail\Config;

/*
 * Global Mailer configuration constants.
 */
final readonly class MailerConfig
{
    public const string MAIL_DOMAIN = "@goralys.fr";
    public const string BASE_ALIAS = "no-reply";
    public const string SUPPORT_ALIAS = "support";
    public const array ALIASES_DISPLAY_NAME = [
        self::BASE_ALIAS => "Goralys",
        self::SUPPORT_ALIAS => "Goralys - Support",
    ];
}
