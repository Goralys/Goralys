<?php

namespace Goralys\Core\User\Repository;

use DateInterval;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTime;
use Goralys\Core\User\Data\AuthToken;
use Goralys\Core\User\Data\AuthTokensCollection;
use Goralys\Core\User\Repository\Interfaces\AuthRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use mysqli_result;

final class AuthRepository implements AuthRepositoryInterface
{
    private DbContainerInterface $db;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, DbContainerInterface $db)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Gets all the auth tokens for a given user.
     * @param string $username The user to gets all the tokens for.
     * @return AuthTokensCollection All the auth tokens for the given user.
     * @throws DateMalformedStringException
     */
    public function getTokens(string $username): AuthTokensCollection
    {
        $result = $this->db->fetch(
            "select username, name, expires_at, created_at from auth_tokens
                  where username = ? and (expires_at > now())",
            "s",
            $username
        );
        return $this->buildTokensFromResult($result);
    }

    /**
     * Builds a collection of auth tokens from a raw mysqli fetch result.
     * The provided result should (at least) contain these four fields:
     * 'username', 'name', 'expires_at' and 'created_at'.
     * @param mysqli_result $result The raw result.
     * @return AuthTokensCollection The list of tokens extracted from the result.
     * @throws DateMalformedStringException
     */
    private function buildTokensFromResult(mysqli_result $result): AuthTokensCollection
    {
        $c = new AuthTokensCollection();
        while ($row = $result->fetch_assoc()) {
            $c->addToken(new AuthToken(
                $row['username'],
                $row['name'],
                new DateTime($row['expires_at']),
                new DateTime($row['created_at'])
            ));
        }
        return $c;
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
            "insert into auth_tokens (username, token_hash, name, expires_at) 
                   values (?, ?, ?, ?)",
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
                   where username = ? and token_hash = ? and (expires_at > now())",
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
                   where username = ? and token_hash = ? and (expires_at > now())",
            "ss",
            $username,
            $tokenHash
        )->num_rows > 0;
    }

    /**
     * Revokes an auth token.
     * @param string $username The user to which the token belongs.
     * @param string $name The name of the token to revoke.
     * @return bool Whether the operation succeded.
     */
    public function revokeToken(string $username, string $name): bool
    {
        return $this->db->run(
            "delete from auth_tokens
                  where username = ? and name = ?",
            "ss",
            $username,
            $name
        );
    }
}
