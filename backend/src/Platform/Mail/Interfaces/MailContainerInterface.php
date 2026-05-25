<?php

namespace Goralys\Platform\Mail\Interfaces;

interface MailContainerInterface
{
    public function sendMail(string $alias, string $subject, string $content, string $to);

}
