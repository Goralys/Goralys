<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Data;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\VirtualUserDTO;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use JsonSerializable;

/**
 * The DTO used to send the users to the frontend
 */
final readonly class UserGetDTO implements JsonSerializable
{
    /**
     * @param string $username The username (partially obfuscated) of the user.
     * @param string $fullName The full name of the user.
     * @param UserRole $role The role of the user.
     * @param string $publicId The public id of the user (uuid).
     */
    public function __construct(
        public string $username,
        public string $fullName,
        public UserRole $role,
        public string $publicId,
    ) {
    }

    /**
     * Returns a {@see UserGetDTO}, which is used to transfer data to the frontend, from {@see UserFullDTO}.
     * @param UserFullDTO $user The user's complete information
     * @param string $publicId The public id of the user.
     * @return self The data to send to the frontend.
     */
    public static function fromFull(UserFullDTO $user, string $publicId): self
    {
        $username = $user->username;
        $obfuscated = substr($username, 0, 2) . str_repeat('*', max(2, strlen($username) - rand(3, 7) - 2));

        return new self(
            $obfuscated,
            $user->fullName,
            $user->role,
            $publicId,
        );
    }

    /**
     * Returns a {@see UserGetDTO}, which is used to transfer data to the frontend, from {@see VirtualUserDTO}.
     * @param VirtualUserDTO $user The user's complete information
     * @param string $publicId The public id of the user.
     * @return self The data to send to the frontend.
     */
    public static function fromVirtual(VirtualUserDTO $user, string $publicId): self
    {
        $username = $user->username;
        $obfuscated = substr($username, 0, 2) . str_repeat('*', max(2, strlen($username) - rand(3, 7) - 2));

        return new self(
            $obfuscated,
            UsernameFormatterService::formatUsername($user->username),
            $user->role,
            $publicId,
        );
    }

    /**
     * Transforms the DTO to a comprehensive JSON array that can then be sent to the frontend.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'username' => $this->username,
            'fullName' => $this->fullName,
            'role' => $this->role->toString(),
            'publicId' => $this->publicId,
        ];
    }
}
