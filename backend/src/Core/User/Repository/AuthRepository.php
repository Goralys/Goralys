<?php

namespace Goralys\Core\User\Repository;

use DateInterval;
use DateMalformedIntervalStringException;
use DateTime;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;

class AuthRepository implements Interfaces\AuthRepositoryInterface
{
    private DbContainerInterface $db;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, DbContainerInterface $db)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Adds a new auth token for a given user.
     * @param string $username The user to add the token for.
     * @param string $tokenHash The hashed token.
     * @param string $name The name of the token. This is used to easily differenciate tokens even after rotation(s).
     * @return bool Whether the operation succeeded or not.
     */
    public function addToken(string $username, string $tokenHash, string $name): bool
    {
        $expires = $this->getExpire('add', $username);
        if (!$expires) {
            return false;
        }

        return $this->db->run(
            "insert into auth_tokens (username, token_hash, name, active, expires_at) 
                   values (?, ?, ?, true, ?)",
            "ssss",
            $username,
            $tokenHash,
            $name,
            $expires
        );
    }

    /**
     * Determines the expiry date of an auth token.
     * @param string $action The action that the expire date is required for.
     * @param string $initiator The user that is trying to perform the action.
     * @return DateTime|null Null if the function fails to determine the expiry date. The expiry date elsewhise.
     */
    private function getExpire(string $action, string $initiator): ?DateTime
    {
        $expires = new DateTime();
        try {
            $expires->add(new DateInterval('P' . Config::AUTH::AUTH_TOKEN_LIFETIME . 'D'));
        } catch (DateMalformedIntervalStringException $e) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "Could not $action auth token for $initiator, failed to determine token expire date: \n"
                . $e->getMessage()
            );
            return null;
        }
        return $expires;
    }

    /**
     * Rotates a given auth token (hashed). This operation must be atomic:
     * the old token is invalidated if and only if the new one is successfully stored.
     * @param string $username The user the token belongs to.
     * @param string $old The old token hash to invalidate.
     * @param string $new The new token hash to store.
     * @return bool Whether the operation succeeded or not.
     */
    public function rotateToken(string $username, string $old, string $new): bool
    {
        $expires = $this->getExpire('rotate', $username);
        if (!$expires) {
            return false;
        }

        return $this->db->run(
            "update auth_tokens
                   set token_hash = ?, expires_at = ?
                   where username = ? and token_hash = ? and active = true and (expires_at > now())",
            "ssss",
            $new,
            $expires,
            $username,
            $old
        );
    }

    /**
     * Checks if a token is valid or not.
     * @param string $username The user to which the token belongs.
     * @param string $tokenHash The hash of the token to validate.
     * @return bool Whether the token is valid.
     */
    public function isTokenValid(string $username, string $tokenHash): bool
    {
        return $this->db->fetch(
            "select 1 from auth_tokens
                   where username = ? and token_hash = ? and active = true and (expires_at > now())",
            "ss",
            $username,
            $tokenHash
        )->num_rows > 0;
    }
}
