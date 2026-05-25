<?php

namespace Goralys\Platform\Mail\Data;

/*
 * The DTO used to transport email information.
 */

final readonly class MailDTO
{
    /**
     * @param MailAccountDTO $from The account from which the mail is sent.
     * @param string $subject The subject of the mail.
     * @param string $body The body (content) of the mail.
     * @param string[] $to The recipient(s) of the mail.
     */
    public function __construct(
        public MailAccountDTO $from,
        public string $subject,
        public string $body,
        public array $to,
    ) {}
}
