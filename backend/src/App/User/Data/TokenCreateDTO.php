<?php

namespace Goralys\App\User\Data;

final readonly class TokenCreateDTO
{
    /**
     * @param string $username The user to which the token is issued.
     * @param string $name The 'name' of the token (~= device name)
     */
    public function __construct(
        public string $username,
        public string $name
    ) {
    }
}
