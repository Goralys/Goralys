<?php

namespace Goralys\Core\User\Data;

use DateTime;
use Goralys\Shared\Lib\GoralysLib as Lib;
use JsonSerializable;

/**
 * DTO used to represent an auth token
 */
final readonly class AuthToken implements JsonSerializable
{
    /**
     * @param string $username The user to which the token belongs.
     * @param string $name The name of the token.
     * @param DateTime $expires The time when the token will expire.
     * @param DateTime $created The time when the token was issued.
     */
    public function __construct(
        public string $username,
        public string $name,
        public DateTime $expires,
        public DateTime $created
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            "username" => Lib::STRING::obfuscate($this->username, 2),
            "name" => $this->name,
            "expires" => $this->expires,
            "created" => $this->created,
        ];
    }
}
