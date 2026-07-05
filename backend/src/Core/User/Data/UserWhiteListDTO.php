<?php

namespace Goralys\Core\User\Data;

use Goralys\Shared\User\Data\FullNameDTO;

/**
 * This DTO is used to whitelist users. This is done by inserting them in a temporary table before they are created.
 */
final readonly class UserWhiteListDTO
{
    /**
     * @param string $username The username of the user.
     * @param FullNameDTO $fullName The fullname of the user.
     */
    public function __construct(
        public string $username,
        public FullNameDTO $fullName
    ) {
    }
}
