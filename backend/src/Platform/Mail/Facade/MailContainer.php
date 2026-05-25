<?php

namespace Goralys\Platform\Mail\Facade;

use Goralys\Platform\Loader\Services\EnvService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Platform\Mail\Config\MailerConfig;
use Goralys\Platform\Mail\Data\MailAccountDTO;
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

    /**
     * @param EnvService $env The injected environnement.
     * @param LoggerInterface $logger The injected logger.
     */
    public function __construct(EnvService $env, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = new MailConfigDTO(
            $env->getByKey("MAIL_HOST"),
            $env->getByKey("MAIL_PORT"),
            $env->getByKey("MAIL_USERNAME"),
            $env->getByKey("MAIL_PASSWORD"),
        );

        $this->sender = new MailSendService();
        $this->adminMail = $env->getByKey("MAIL_ADMIN_ADDRESS");
    }

    /**
     * Sends an email using a dedicated service.
     * @param string $alias The alias used to send the email. Refer to {@see MailerConfig} for supported aliases.
     * @param string $subject The subject of the email.
     * @param string $content The content of the email.
     * @param string $to The recipient of the email. If set to `"@admin"`, the email will be sent to all admins
     * configured inside the environment file.
     * @throws Exception If the email cannot be sent correctly.
     */
    public function sendMail(string $alias, string $subject, string $content, string $to): void
    {
        $account = new MailAccountDTO($alias . MailerConfig::MAIL_DOMAIN, MailerConfig::ALIASES_DISPLAY_NAME[$alias]);
        $recipients = [$to];
        if (trim($to) === "@admin") {
            $recipients = explode(",", $this->adminMail);
        }
        $this->sender->send($this->config, new MailDTO($account, $subject, $content, $recipients));
        $this->logger->debug(LoggerInitiator::PLATFORM, "Sent email (" . $subject . ") to: " . $to);
    }
}
