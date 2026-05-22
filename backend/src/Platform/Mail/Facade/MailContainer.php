<?php

namespace Goralys\Platform\Mail\Facade;

use Goralys\Platform\Loader\Services\EnvService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Mail\Data\MailConfigDTO;
use Goralys\Platform\Mail\Data\MailDTO;
use Goralys\Platform\Mail\Interfaces\MailContainerInterface;
use Goralys\Platform\Mail\Services\MailSendService;
use PHPMailer\PHPMailer\Exception;

class MailContainer implements MailContainerInterface
{
    private LoggerInterface $logger;
    private MailSendService $sender;
    private MailConfigDTO $config;
    private string $adminMail;

    public function __construct(EnvService $env, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = new MailConfigDTO(
            $env->getByKey("MAIL_HOST"),
            $env->getByKey("MAIL_PORT"),
            $env->getByKey("MAIL_USERNAME"),
            $env->getByKey("MAIL_PASSWORD"),
            $env->getByKey("MAIL_FROM"),
        );

        $this->sender = new MailSendService();
        $this->adminMail = $env->getByKey("MAIL_ADMIN_ADDRESS");
    }

    /**
     * @throws Exception
     */
    public function sendMail(string $subject, string $content, string $to): void
    {
        $recipients = [$to];
        if (trim($to) === "@admin") {
            $recipients = explode(",", $this->adminMail);
        }
        $this->sender->send($this->config, new MailDTO($subject, $content), ...$recipients);
        $this->logger->debug(LoggerInitiator::PLATFORM, "Sent email (" . $subject . ") to: " . $to);
    }
}
