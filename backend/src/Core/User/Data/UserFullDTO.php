<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Data;

use DateTime;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\User\Data\FullNameDTO;
use JsonSerializable;

/**
 * The DTO containing all the information of a user
 */
final readonly class UserFullDTO implements JsonSerializable
{
    /**
     * @param int $id The unique database ID of the user.
     * @param string $username The username of the user.
     * @param string $publicId The public id of the user.
     * @param UserRole $role The role of the user.
     * @param FullNameDTO $fullName The full name of the user.
     * @param string $email The email of the user.
     * @param DateTime $createdAt The date the user was created at.
     */
    public function __construct(
        public int $id,
        public string $username,
        public string $publicId,
        public UserRole $role,
        public FullNameDTO $fullName,
        public string $email,
        public DateTime $createdAt
    ) {
    }

    /**
     * Transforms the user's profile into a simple JSON encodable array containing all necessary information for
     * the frontend. This function also obfuscated the user's username.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => Lib::STRING::obfuscate($this->username, 2),
            'pubId' => $this->publicId,
            'fullName' => (string) $this->fullName,
            'role' => $this->role->toString(),
            'email' => $this->email,
            'createdAt' => $this->createdAt
        ];
    }
}
