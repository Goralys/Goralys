<?php

namespace Goralys\App\User\Data;

final readonly class RevokeTokenDTO
{
    /**
     * @param string $username The user to which the token belongs.
     * @param string $name The name of the token (≈ device name).
     */
    public function __construct(
        public string $username,
        public string $name,
    ) {
    }
}
