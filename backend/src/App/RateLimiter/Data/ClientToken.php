<?php

namespace Goralys\App\RateLimiter\Data;

/**
 * The DTO used by the rate limiter when forging tokens for each client.
 */
final readonly class ClientToken
{
    /**
     * @param string $token The raw token that identifies the client.
     * @param string $pure The pure radical (ip) associated with the token.
     * @param bool $isPure Wether the token is considered "pure". A "pure" token means that it is basically the user's
     * ip. Pure tokens are treated differently because of shared networks, thus they have a higher than normal limit.
     */
    public function __construct(
        public string $token,
        public string $pure,
        public bool $isPure,
    ) {
    }
}
