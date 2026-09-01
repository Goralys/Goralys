<?php

namespace Goralys\Core\User\Data;

use JsonSerializable;

/**
 * A special object used to represent a list of authentication tokens.
 */
final class AuthTokensCollection implements JsonSerializable
{
    /**
     * @param AuthToken[] $tokens The intial tokens inside the collection.
     */
    public function __construct(
        private(set) array $tokens = []
    ) {
    }

    /**
     * Adds a new auth token to the collection.
     * @param AuthToken $token The token to add.
     * @return void
     */
    public function addToken(AuthToken $token): void
    {
        $this->tokens[] = $token;
    }

    /**
     * Transforms the tokens into a JSON array.
     * @return AuthToken[] The JSON encoded tokens.
     */
    public function jsonSerialize(): array
    {
        return $this->tokens;
    }
}
