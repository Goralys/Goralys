<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Repository;

use Exception;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Data\VirtualUserDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\DB\GoralysDBException;
use Goralys\Shared\Exception\User\UserNotFoundException;
use Goralys\Shared\User\Data\FullNameDTO;
use mysqli_result;

/**
 * The repository used to get user's info from the database.
 */
final class UserRepository implements UserRepositoryInterface
{
    private LoggerInterface $logger;
    private DbContainerInterface $db;

    /**
     * Initializes the logger and the database container for the repository.
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
    ) {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Get a user's info with its username.
     * @param string $username The user's username.
     * @return UserFullDTO The user's info.
     * @throws UserNotFoundException If the user is invalid.
     */
    public function getByUsername(string $username): UserFullDTO
    {
        $result = $this->db->fetch(
            "select id, u.username, role, firstname, lastname, email from users u
                   left join emails e on u.username = e.username
                   right outer join users_info ui on ui.username = u.username
                   where u.username = ?",
            "s",
            $username,
        );

        return $this->buildUserFromResult($result);
    }

    /**
     * This helper is used to build a user DTO containing all the user's info (full) from a database request's result,
     * it is in charge of the logging process.
     * @param mysqli_result $result The result from the database.
     * @return UserFullDTO All the user's info.
     * @throws UserNotFoundException If the user is invalid.
     */
    private function buildUserFromResult(mysqli_result $result): UserFullDTO
    {
        if ($result->num_rows === 0) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "Failed to fetch the user's data from the database.",
            );
            throw new UserNotFoundException("Invalid user provided.");
        }

        return $this->buildUserFromRow($result->fetch_assoc());
    }

    /**
     * This helper is used to build a user DTO containing all the user's info (full) from a database row.
     * It is in charge of the logging process.
     * @param array $row The row from the database result.
     * @return UserFullDTO All the user's info.
     */
    private function buildUserFromRow(array $row): UserFullDTO
    {
        $this->logger->info(
            LoggerInitiator::CORE,
            "User's data were successfully fetched for user : " . $row['username'] . " - Data:\n" . print_r($row, true),
        );
        return new UserFullDTO(
            (int) $row['id'],
            $row['username'],
            UserRole::fromString($row['role']),
            new FullNameDTO($row['firstname'], $row['lastname']),
            $row['email'] ?? "",
        );
    }

    /**
     * Saves a new user to the database.
     * @param UserCreateDTO $userData The necessary data to save the user.
     * @return bool If the save was successful or not.
     */
    public function save(UserCreateDTO $userData): bool
    {
        return $this->db->run(
            "insert into users (username, password_hash, role) values (?, ?, ?)",
            "sss",
            $userData->username,
            $userData->passwordHash,
            $userData->role->toString(),
        );
    }

    /**
     * Checks if a user exists inside the database.
     * @param string $username The user's username.
     * @return bool If the user exits or not.
     */
    public function exists(string $username): bool
    {
        return $this->db->fetch(
            "select 1 from users where username = ? limit 1",
            "s",
            $username,
        )->num_rows != 0;
    }

    /**
     * Checks if a username is valid.
     * @param string $username The user's username.
     * @return bool If the username is valid or not.
     */
    public function isUsernameValid(string $username): bool
    {
        return $this->db->fetch(
            "select 1 from users_info where username = ? limit 1",
            "s",
            $username,
        )->num_rows != 0;
    }

    /**
     * Checks if a user exits with a given username.
     * If it exists, it returns a new login DTO.
     * @param string $username
     * @return UserLoginDTO|null
     */
    public function getLoginDTO(string $username): ?UserLoginDTO
    {
        $result = $this->db->fetch(
            "select password_hash from users where username = ?",
            "s",
            $username,
        );

        if ($result->num_rows === 0) {
            $this->logger->warning(
                LoggerInitiator::CORE,
                "Failed to connect user, invalid username : " . $username,
            );
            return null;
        }

        $row = $result->fetch_assoc();

        return new UserLoginDTO($username, $row['password_hash']);
    }

    /**
     * Gets the role of a user.
     * @param string $username The user's username.
     * @return ?UserRole The user's role.
     */
    public function getRoleForUsername(string $username): ?UserRole
    {
        $result = $this->db->fetch(
            "select username, role from (
            select student_username as username, 'student' as role
            from student_topics
            union all
            select teacher_username as username, 'teacher' as role
            from topic_teachers
            union all
            select username as username, 'admin' as role
            from admins_list
            ) as all_ids
            where username = ?
            limit 1",
            "s",
            $username,
        );

        if ($result->num_rows === 0) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "No such user : " . $username,
            );
            return null;
        }

        $role = $result->fetch_assoc()['role'];
        return UserRole::fromString($role);
    }

    /**
     * Gets the full name of a user.
     * @param string $username The user's username.
     * @return ?FullNameDTO The user's full name or null if the user does not exist.
     */
    public function getFullNameForUsername(string $username): ?FullNameDTO
    {
        $result = $this->db->fetch(
            "select firstname, lastname from users_info where username = ?
            limit 1",
            "s",
            $username,
        );

        if ($result->num_rows === 0) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "No such user : " . $username,
            );
            return null;
        }
        $row = $result->fetch_assoc();
        return new FullNameDTO($row['firstname'], $row['lastname']);
    }

    /**
     * Deletes all users (except admins) from the database.
     * @return bool If the deletion was successful.
     */
    public function clearAll(): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->runNoArgs(
                "delete from public_ids where username not in (
                       select username from users where role = 'admin'
                       union
                       select username from admins_list)",
            );
            $this->db->runNoArgs(
                "delete from users_info where username not in (
                       select username from users where role = 'admin'
                       union
                       select username from admins_list)",
            );
            $this->db->runNoArgs("delete from users where role <> 'admin'");
            $this->db->commit();
            return true;
        } catch (Exception) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get a user's info with its public id.
     * @param string $uuid The public id of the user.
     * @return UserFullDTO The info of the user.
     * @throws UserNotFoundException If the user does not exist in the DB.
     */
    public function getByPublicId(string $uuid): UserFullDTO
    {
        $result = $this->db->fetch(
            "select u.id, u.username, u.role, ui.firstname, ui.lastname, e.email 
                   from users u
                   join users_info ui on ui.username = u.username
                   join public_ids pi on u.username = pi.username
                   left join emails e on u.username = e.username
                   where pi.public_id = ?",
            "s",
            $uuid,
        );

        return $this->buildUserFromResult($result);
    }

    /**
     * Get if a public id belongs to a valid user.
     * @param string $uuid The public id.
     * @return bool If the public id is valid or not.
     */
    public function isPublicIdValid(string $uuid): bool
    {
        return $this->db->fetch(
            "select 0 from public_ids where public_id = ?",
            "s",
            $uuid,
        )->num_rows !== 0;
    }

    /**
     * Retrieves the public id of a user from its username.
     * @param string $username The username of the user.
     * @return ?string The public id on success, `null` on failure.
     */
    public function getPublicIdForUsername(string $username): ?string
    {
        return $this->db->fetch(
            "select public_id from public_ids where username = ?",
            "s",
            $username,
        )->fetch_assoc()['public_id'] ?? null;
    }

    /**
     * @param string $publicId The user's public id.
     * @return ?string The user's username, or null if the user does not exist.
     */
    public function getUsernameForPublicId(string $publicId): ?string
    {
        return $this->db->fetch(
            "select username from public_ids where public_id = ?",
            "s",
            $publicId,
        )->fetch_assoc()['username'] ?? null;
    }

    /**
     * Returns all the users inside the database.
     * @return UserFullDTO[] The users.
     */
    public function getAll(): array
    {
        $result = $this->db->fetchNoArgs(
            "select id, u.username, firstname, lastname, role 
                   from users u
                   join users_info ui on ui.username = u.username
                   where role <> 'admin'"
        );
        return $this->buildUsersFromResult($result);
    }

    /**
     * This helper is used to build multiple users DTO containing all the users' info (full) from a database request's
     * result, it is in charge of the logging process.
     * @param mysqli_result $result The result from the database.
     * @return UserFullDTO[] All the users' info.
     */
    private function buildUsersFromResult(mysqli_result $result): array
    {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $this->buildUserFromRow($row);
        }
        return $users;
    }

    /**
     * Unlike {@see UserRepository::hardDelete()}, this deletes a user only from the `users` table.
     * This is used to reset the user's password.
     * @param string $username The user's username.
     * @return bool Whether the deletion was successful.
     */
    public function softDelete(string $username): bool
    {
        return $this->db->run(
            "delete from users where username = ?",
            "s",
            $username,
        );
    }

    /**
     * Unlike {@see UserRepository::softDelete()}, this deletes a user from all the database's tables.
     * This is used to completely remove a user and its associated subjects (students) and topics (teachers).
     * @param string $username The user's username.
     * @return bool Whether the deletion was successful.
     */
    public function hardDelete(string $username): bool
    {
        $this->db->beginTransaction();
        try {
            // Check the README.md at the project's root to have a better understanding of the cascading effects
            // mentioned bellow. Or, if you hate yourself, you can check the raw schema at backend/data_structure.sql

            // cascades to student_topics and topic_teachers, ignore topics shared by multiple teachers
            $this->db->runIgnoreNoOps(
                "delete from topics where id in (
                select topic_id from topic_teachers where teacher_username = ?
                and topic_id not in (
                    select topic_id from topic_teachers where teacher_username <> ?
                )
            )",
                "ss",
                $username,
                $username,
            );

            // cascades to all other tables
            if (!$this->db->run("delete from users_info where username= ?", "s", $username)) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (GoralysDBException $e) {
            $this->db->rollback();
            $this->logger->error(LoggerInitiator::CORE, "Failed to hard-delete user $username: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replaces a teacher inside the database, the new teacher's username will replace the old one, and all the subjects
     * will remain linked correctly to that new teacher.
     * @param string $old The old teacher's username.
     * @param string $new The new teacher's username.
     * @return bool Whether the replacement is successful.
     */
    public function replaceTeacher(string $old, string $new): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->run(
                "update topic_teachers set teacher_username = ? where teacher_username = ?",
                "ss",
                $new,
                $old
            );
            $this->db->run(
                "update public_ids set username = ?, public_id = uuid() where username = ?",
                "ss",
                $new,
                $old
            );

            $this->db->commit();
            return true;
        } catch (Exception) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Returns all uncreated users from the database.
     * @return VirtualUserDTO[] The virtual users.
     */
    public function getVirtual(): array
    {
        $result = $this->db->fetchNoArgs(
            "select distinct all_ids.username, all_ids.role from (
                   select student_username as username, 'student' as role from student_topics
                   union
                   select teacher_username as username, 'teacher' as role from topic_teachers
                   ) as all_ids where all_ids.username not in (select username from users)",
        );

        return $this->buildVirtualUsersFromResult($result);
    }

    /**
     * This helper is used to build multiple virtual user DTOs from a database request's result,
     * it is in charge of the logging process.
     * @param mysqli_result $result The result from the database.
     * @return VirtualUserDTO[] All the virtual users' info.
     */
    private function buildVirtualUsersFromResult(mysqli_result $result): array
    {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $this->buildVirtualUserFromRow($row);
        }
        return $users;
    }

    /**
     * This helper is used to build a virtual user DTO from a database row.
     * @param array $row The row from the database result.
     * @return VirtualUserDTO The virtual user's info.
     */
    private function buildVirtualUserFromRow(array $row): VirtualUserDTO
    {
        $this->logger->info(
            LoggerInitiator::CORE,
            "Virtual user's data were successfully fetched for user : " . $row['username'],
        );
        return new VirtualUserDTO(
            $row['username'],
            UserRole::fromString($row['role']),
        );
    }

    /**
     * Returns a map of all users' public ids indexed by their username.
     * @return array<string, string> A username => public_id map.
     */
    public function getPublicIds(): array
    {
        $result = $this->db->fetchNoArgs("select username, public_id from public_ids");
        $map = [];
        while ($row = $result->fetch_assoc()) {
            $map[$row['username']] = $row['public_id'];
        }
        return $map;
    }

    /**
     * Returns all admins from the database.
     * @return UserFullDTO[] The admins.
     */
    public function getAdmins(): array
    {
        $result = $this->db->fetchNoArgs(
            "select id, u.username, firstname, lastname, role 
                   from users u
                   join users_info ui on ui.username = u.username
                   where role = 'admin'"
        );
        return $this->buildUsersFromResult($result);
    }

    /**
     * Returns all uncreated admins from the database.
     * These are users present in admins_list but not yet in the user table.
     * @return VirtualUserDTO[] The virtual admins.
     */
    public function getVirtualAdmins(): array
    {
        $result = $this->db->fetchNoArgs(
            "select username, 'admin' as role from admins_list 
                where username not in (select username from users)",
        );
        return $this->buildVirtualUsersFromResult($result);
    }

    /**
     * Creates a new potential admin in the database.
     * @param string $username The new admin's username.
     * @return bool Whether the creation was successful.
     */
    public function addAdmin(string $username): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->run("insert into admins_list (username) values (?)", "s", $username);

            $this->db->commit();
            return true;
        } catch (Exception) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Deletes an admin in the database.
     * @param string $username The new admin's username.
     * @return bool Whether the deletion was successful.
     */
    public function revokeAdmin(string $username): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->run("delete from public_ids where username = ?", "s", $username);
            $this->db->run("delete from admins_list where username = ?", "s", $username);
            $this->db->run("delete from users where username = ?", "s", $username);

            $this->db->commit();
            return true;
        } catch (Exception) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Gets a user's email from the database.
     * @param string $username The username of the user.
     * @return ?string The email of the user (or `null` if it has no email).
     */
    public function getEmail(string $username): ?string
    {
        $result = $this->db->fetch(
            "select email from emails where username = ?",
            "s",
            $username
        );
        return $result->fetch_assoc()['email'] ?? null;
    }

    /**
     * Sets a user's email address inside the database.
     * @param string $username The username of the user.
     * @param string $email The new email for this user
     * @return bool Whether the update was successful.
     */
    public function setEmail(string $username, string $email): bool
    {
        return $this->db->runIgnoreNoOps(
            "insert into emails (username, email) values (?, ?)
                   on duplicate key update email = values(email)",
            "ss",
            $username,
            $email,
        );
    }

    /**
     * Deletes a user's email address inside the database.
     * @param string $username The username of the user.
     * @return bool Whether the deletion was successful.
     */
    public function removeEmail(string $username): bool
    {
        return $this->db->run(
            "delete from emails where username = ?",
            "s",
            $username,
        );
    }

    /**
     * Whitelists a user by inserting it inside a temporary table (`users_info`).
     * The user can then be created via the {@see UserRepositoryInterface::save()} method.
     * @param string $username The username of the user to whitelist.
     * @param FullNameDTO $fullName The fullname of the user to whitelist.
     * @return bool If the operation was successful or not.
     */
    public function whitelist(string $username, FullNameDTO $fullName): bool
    {
        return $this->db->runIgnoreNoOps(
            "insert ignore into users_info (username, firstname, lastname) values (?, ?, ?)",
            "sss",
            $username,
            $fullName->first,
            $fullName->last
        );
    }
}
