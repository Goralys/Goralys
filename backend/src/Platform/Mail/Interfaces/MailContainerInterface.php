<?php

namespace Goralys\Platform\Mail\Interfaces;

use Goralys\Platform\Mail\Data\MailDTO;

interface MailContainerInterface
{
    public function sendMail(string $subject, string $content, string $to);

}
