<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Controllers;

use Goralys\App\User\Data\UserCollection;
use Goralys\App\User\Data\UserGetDTO;
use Goralys\App\User\Data\UsernameTable;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\VirtualUserDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Core\User\Repository\UserRepository;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\Exception\User\GoralysUserException;
use Goralys\Shared\Lib\GoralysLib as Lib;
use Goralys\Shared\Lib\String\StringCase;
use Goralys\Shared\User\Data\FullNameDTO;

/**
 * The controller that handles the user logic.
 */
final class UserController
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;
    private UserRepositoryInterface $repo;
    private UsernameManager $usernames;

    /**
     * Initializes the logger and the database container used by the controller.
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
    ) {
        $this->logger = $logger;
        $this->db = $db;

        $this->repo = new UserRepository($this->logger, $this->db);
        $this->usernames = new UsernameManager($this->repo);
    }

    /**
     * Deletes all users (except admins) from the database.
     * @return bool If the deletion was successful
     */
    public function clear(): bool
    {
        // clean username list first
        if ($raw = file(Config::USER::USERNAME_LIST_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {
            $result = array_filter($raw, function ($line) {
                [$fullName, $username] = explode("=", $line);
                if (is_string($fullName) && is_string($username)) {
                    return str_contains($username, Config::USER::ADMIN_SUFFIX);
                }
                return false;
            });
            file_put_contents(Config::USER::USERNAME_LIST_PATH, implode(PHP_EOL, $result));
        }
        return $this->repo->clearAll();
    }

    /**
     * Returns all non-admin users from the database.
     * @return UserCollection The users (teachers and students).
     */
    public function getAll(): UserCollection
    {
        return $this->buildCollection($this->repo->getAll(), UserGetDTO::fromFull(...));
    }

    /**
     * Builds a {@see UserCollection} from an array of user DTOs using the provided mapping callable.
     * @param VirtualUserDTO[]|UserFullDTO[] $users The users to build the collection from.
     * @param callable $fromDTO The callable used to map each user to a {@see UserGetDTO}.
     * @return UserCollection The built collection.
     */
    private function buildCollection(array $users, callable $fromDTO): UserCollection
    {
        $publicIds = $this->repo->getPublicIds();
        $collection = new UserCollection();
        foreach ($users as $user) {
            // Let PHP throw because all users should have a public id, even uncreated ones.
            $collection->addUser($fromDTO($user, $publicIds[Lib::STRING::sanitize(
                $user->username,
                StringCase::LOWER,
            )]));
        }
        return $collection;
    }

    /**
     * Returns all uncreated non-admin users from the database.
     * @return UserCollection The uncreated users (teachers and students).
     */
    public function getVirtual(): UserCollection
    {
        return $this->buildCollection($this->repo->getVirtual(), UserGetDTO::fromVirtual(...));
    }

    /**
     * Returns all admin users from the database.
     * @return UserCollection The users (admins).
     */
    public function getAdmins(): UserCollection
    {
        return $this->buildCollection($this->repo->getAdmins(), UserGetDTO::fromFull(...));
    }

    /**
     * Returns all uncreated admin users from the database.
     * @return UserCollection The uncreated users (admins).
     */
    public function getAdminsVirtual(): UserCollection
    {
        return $this->buildCollection($this->repo->getVirtualAdmins(), UserGetDTO::fromVirtual(...));
    }

    /**
     * Adds a new admin inside the database.
     * @param FullNameDTO $name The full name of the admin to add.
     * @return ?string The admin's username on success, null otherwise.
     * @throws GoralysUserException
     */
    public function addAdmin(FullNameDTO $name): ?string
    {
        $table = new UsernameTable();
        $username = $table->resolve($name, UserRole::ADMIN);
        return ($this->repo->whitelist($username, $name) && $this->repo->addAdmin($username)) ? $username : null;
    }

    /**
     * Revokes a new admin inside the database.
     * @param string $publicId The public id of the admin to revoke.
     * @return bool Whether the creation was successful.
     * @throws GoralysRuntimeException If the admin's username could not be retrieved.
     */
    public function revokeAdmin(string $publicId): bool
    {
        return $this->repo->revokeAdmin($this->usernames->get($publicId));
    }

    /**
     * Deletes a user partially (consult {@see UserRepositoryInterface::softDelete()} for more information) to allow it
     * to recreate his account and thus choose a new password.
     * @param string $publicId The user's public id.
     * @return bool Whether the operation was successful.
     * @throws GoralysRuntimeException If the username of the user could not be retrieved.
     */
    public function reset(string $publicId): bool
    {
        return $this->repo->softDelete($this->usernames->get($publicId));
    }

    /**
     * Replaces a teacher inside the database.
     * @param string $publicId The current teacher's public id.
     * @param string $newName The full name of the new teacher.
     * @return ?string Whether the operation was successful.
     * @throws GoralysRuntimeException|GoralysUserException If the username of the user could not be retrieved.
     */
    public function replaceTeacher(string $publicId, string $newName): ?string
    {
        $table = new UsernameTable();
        $old = $this->usernames->get($publicId);
        $new = $table->resolve($newName);
        return ($this->repo->softDelete($old) && $this->repo->replaceTeacher($old, $new)) ? $new : null;
    }

    /**
     * Deletes a user completely (consult {@see UserRepositoryInterface::hardDelete()} for more information).
     * @param string $publicId The user's public id.
     * @return bool Whether the operation was successful.
     * @throws GoralysRuntimeException If the username of the user could not be retrieved.
     */
    public function delete(string $publicId): bool
    {
        $target = $this->usernames->get($publicId);
        $this->logger->info(
            LoggerInitiator::CORE,
            "Attempting to delete user " . $target . " (initiator: " . $_SESSION[Config::SESSION::USERNAME]
        );
        return $this->repo->hardDelete($target);
    }

    /**
     * Gets the email for the current user or the provided one.
     * @param $publicId ?string The public id of the target user, if `null` (default),
     * the controller will query the email for the current user.
     * @return ?string The email of the user (or `null` if it has no email).
     * @throws GoralysRuntimeException If the username of the user cannot be retrieved.
     */
    public function getEmail(?string $publicId = null): ?string
    {
        return $this->repo->getEmail(
            $publicId ? $this->usernames->get($publicId)
            : $_SESSION[Config::SESSION::USERNAME]
        );
    }

    /**
     * Sets the email for the current user.
     * @param string $email The new email.
     * @return bool Whether the update was successful.
     */
    public function setEmail(string $email): bool
    {
        if (!$this->repo->setEmail($_SESSION[Config::SESSION::USERNAME], $email)) {
            return false;
        }
        $_SESSION[Config::SESSION::EMAIL] = $email;
        return true;
    }

    /**
     * Deletes the email for the current user.
     * @return bool Whether the deletion was successful.
     */
    public function removeEmail(): bool
    {
        if (!$this->repo->removeEmail($_SESSION[Config::SESSION::USERNAME])) {
            return false;
        }
        unset($_SESSION[Config::SESSION::EMAIL]);
        return true;
    }
}
