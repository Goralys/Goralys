<?php

namespace Goralys\Platform\Mail\Services;

use Goralys\Platform\Mail\Data\MailConfigDTO;
use Goralys\Platform\Mail\Data\MailDTO;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailSendService
{
    /**
     * Sends an email using PHPMailer.
     * @param MailConfigDTO $config The config of the mail connection (host, port, username, etc.).
     * @param MailDTO $mail The mail content and subject.
     * @param string $first The recipient of the email.
     * @param string ...$_to The other optional recipients.
     * @return void
     * @throws Exception If the mail cannot be sent.
     */
    public function send(MailConfigDTO $config, MailDTO $mail, string $first, string ...$_to): void
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();

        $mailer->Host = $config->host;
        $mailer->SMTPAuth = true;
        $mailer->Username = $config->username;
        $mailer->Password = $config->password;
        $mailer->Port = $config->port;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mailer->setFrom($config->username, $config->from);

        foreach ([$first, ...$_to] as $recipient) {
            $mailer->addAddress(trim($recipient));
        }
        $mailer->CharSet = 'UTF-8';
        $mailer->Subject = $mail->subject;
        $signature = file_get_contents(__DIR__ . "/Assets/signature.html") ?? "";
        $mailer->Body = "<p>" . $mail->body . "</p>" . $signature;
        $mailer->isHTML();

        $mailer->send();
    }
}
