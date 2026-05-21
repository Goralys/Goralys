<?php

namespace Goralys\Platform\Mail\Data;

/*
 * The DTO used to transport mail connection configuration.
 */
final readonly class MailConfigDTO
{
    /**
     * @param string $host The host (provider) of your email service.
     * @param int $port The SMTP port on the host.
     * @param string $username Your mail username (e.g. foo@exemple.com).
     * @param string $password Your mail account password.
     * @param string $from The displayed name of the mail sender (e.g. 'Goralys')
     */
    public function __construct(
        public string $host,
        public int $port,
        public string $username,
        public string $password,
        public string $from,
    ) {}
}
