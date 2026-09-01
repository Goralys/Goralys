<?php

namespace Goralys\App\User\Data;

final readonly class TokenLoginDTO
{
    /**
     * @param string $username The usern which is trying to login.
     * @param string $token The token provided by the client.
     */
    public function __construct(
        public string $username,
        public string $token,
    ) {
    }
}
