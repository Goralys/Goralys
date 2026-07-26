<?php

namespace Goralys\Core\User\Repository\Interfaces;

interface AuthRepositoryInterface
{
    /**
     * Adds a new auth token for a given user.
     * @param string $username The user to add the token for.
     * @param string $tokenHash The hashed token.
     * @param string $name The name of the token. This is used to easily differenciate tokens even after rotation(s).
     * @return bool Whether the operation succeeded or not.
     */
    public function addToken(string $username, string $tokenHash, string $name): bool;

    /**
     * Rotates a given auth token (hashed). This operation must be atomic:
     * the old token is invalidated if and only if the new one is successfully stored.
     * @param string $username The user the token belongs to.
     * @param string $old The old token hash to invalidate.
     * @param string $new The new token hash to store.
     * @return bool Whether the operation succeeded or not.
     */
    public function rotateToken(string $username, string $old, string $new): bool;

    /**
     * Checks if a token is valid or not.
     * @param string $username The user to which the token belongs.
     * @param string $tokenHash The hash of the token to validate.
     * @return bool Whether the token is valid.
     */
    public function isTokenValid(string $username, string $tokenHash): bool;
}
