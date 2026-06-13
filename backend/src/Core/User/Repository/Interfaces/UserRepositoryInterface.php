<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Repository\Interfaces;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Data\VirtualUserDTO;
use Goralys\Shared\User\Data\FullNameDTO;

/**
 * Contract for the user repository.
 * Covers all read/write operations on user records in the database.
 */
interface UserRepositoryInterface
{
    /**
     * @param string $username The user's username.
     * @return UserFullDTO The full user record.
     */
    public function getByUsername(string $username): UserFullDTO;

    /**
     * @param string $uuid The user's public UUID.
     * @return UserFullDTO The full user record.
     */
    public function getByPublicId(string $uuid): UserFullDTO;

    /**
     * @param string $username The username to check.
     * @return bool Whether a user with this username exists.
     */
    public function exists(string $username): bool;

    /**
     * Checks whether a username is present in the pre-approved list and therefore allowed to register.
     * @param string $username The username to check.
     * @return bool Whether the username is valid for registration.
     */
    public function isUsernameValid(string $username): bool;

    /**
     * @param string $uuid The public UUID to check.
     * @return bool Whether the UUID corresponds to a known user.
     */
    public function isPublicIdValid(string $uuid): bool;

    /**
     * @param UserCreateDTO $userData The data required to persist the new user.
     * @return bool If the save was successful or not.
     */
    public function save(UserCreateDTO $userData): bool;

    /**
     * @param string $username The user's username.
     * @return UserLoginDTO|null The login credentials, or null if the user does not exist.
     */
    public function getLoginDTO(string $username): ?UserLoginDTO;

    /**
     * @param string $username The user's username.
     * @return ?UserRole The user's role, or null if the user does not exist.
     */
    public function getRoleForUsername(string $username): ?UserRole;

    /**
     * @param string $username The user's username.
     * @return ?FullNameDTO The user's full name, or null if the user does not exist.
     */
    public function getFullNameForUsername(string $username): ?FullNameDTO;

    /**
     * @param string $username The user's username.
     * @return ?string The user's public UUID, or null if the user does not exist.
     */
    public function getPublicIdForUsername(string $username): ?string;

    /**
     * @param string $publicId The user's public id.
     * @return ?string The user's username, or null if the user does not exist.
     */
    public function getUsernameForPublicId(string $publicId): ?string;

    /**
     * Gets all the public associated with their usernames.
     * @return array<string, string> The user's public UUID, or null if the user does not exist.
     */
    public function getPublicIds(): array;

    /**
     * Returns all the users inside the database.
     * @return UserFullDTO[] The users.
     */
    public function getAll(): array;

    /**
     * Returns all the users not yet created inside the database.
     * @return VirtualUserDTO[] The users.
     */
    public function getVirtual(): array;

    /**
     * Creates a new potential admin in the database.
     * @param string $username The new admin's username.
     * @return bool Whether the creation was successful.
     */
    public function addAdmin(string $username): bool;

    /**
     * Deletes an admin in the database.
     * @param string $username The new admin's username.
     * @return bool Whether the deletion was successful.
     */
    public function revokeAdmin(string $username): bool;

    /**
     * Returns all admins from the database.
     * @return UserFullDTO[] The admins.
     */
    public function getAdmins(): array;

    /**
     * Returns all uncreated admins from the database.
     * These are users present in admins_list but not yet in the users table.
     * @return VirtualUserDTO[] The virtual admins.
     */
    public function getVirtualAdmins(): array;

    /**
     * Replaces a teacher inside the database, the new teacher's username will replace the old one, and all the subjects
     * will remain linked correctly to that new teacher.
     * @param string $old The old teacher's username.
     * @param string $new The new teacher's username.
     * @return bool Whether the replacement is successful.
     */
    public function replaceTeacher(string $old, string $new): bool;

    /**
     * Deletes a user only from the `users` table. This is used to reset the user's password.
     * @param string $username The user's username.
     * @return bool Whether the deletion was successful.
     */
    public function softDelete(string $username): bool;

    /**
     * Deletes a user from all the database's tables. This is used to completely remove a user and its associated
     * subjects and topics (teachers only).
     * @param string $username The user's username.
     * @return bool Whether the deletion was successful.
     */
    public function hardDelete(string $username): bool;

    /**
     * Removes all non-admin users from the database.
     * @return bool If the deletion was successful or not.
     */
    public function clearAll(): bool;

    /**
     * Gets a user's email from the database.
     * @param string $username The username of the user.
     * @return ?string The email of the user (or `null` if it has no email).
     */
    public function getEmail(string $username): ?string;

    /**
     * Sets a user's email address inside the database.
     * @param string $username The username of the user.
     * @param string $email The new email for this user.
     * @return bool Whether the update was successful.
     */
    public function setEmail(string $username, string $email): bool;

    /**
     * Deletes a user's email address inside the database.
     * @param string $username The username of the user.
     * @return bool Whether the deletion was successful.
     */
    public function removeEmail(string $username): bool;
}
