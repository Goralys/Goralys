<?php

namespace Goralys\Platform\Mail\Data;

/*
 * The DTO used to transport email information.
 */

final readonly class MailDTO
{
    /**
     * @param string $subject The subject of the mail.
     * @param string $body The body (content) of the mail.
     */
    public function __construct(
        public string $subject,
        public string $body,
    ) {}
}
