<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Shared\User\Data\FullNameDTO;

class FakeUserRepository implements UserRepositoryInterface
{
    private bool $updateResult = true;
    private ?UserFullDTO $userFullDTOResult = null;
    private ?UserLoginDTO $loginDTOResult = null;
    private ?UserRole $roleResult = null;
    private ?FullNameDTO $fullNameResult = null;
    private ?string $emailResult = null;
    private bool $existsResult = false;
    private bool $usernameValidResult = false;
    private array $publicIds = [];
    private array $users = [];

    /**
     * Set the result for update/save operations.
     */
    public function setUpdateResult(bool $updateResult): void
    {
        $this->updateResult = $updateResult;
    }

    /**
     * Set the result returned by {@see getByUsername()}.
     */
    public function setUserFullDTOResult(UserFullDTO $userFullDTOResult): void
    {
        $this->userFullDTOResult = $userFullDTOResult;
    }

    /**
     * Set the result returned by {@see getLoginDTO()}.
     */
    public function setLoginDTOResult(?UserLoginDTO $loginDTOResult): void
    {
        $this->loginDTOResult = $loginDTOResult;
    }

    /**
     * Set the result returned by {@see getRoleForUsername()}.
     */
    public function setRoleResult(?UserRole $roleResult): void
    {
        $this->roleResult = $roleResult;
    }

    /**
     * Set the result returned by {@see getFullNameForUsername()}.
     */
    public function setFullNameResult(?FullNameDTO $fullNameResult): void
    {
        $this->fullNameResult = $fullNameResult;
    }

    /**
     * Set the result returned by {@see getEmail()}.
     */
    public function setEmailResult(?string $emailResult): void
    {
        $this->emailResult = $emailResult;
    }

    public function getByUsername(string $username): UserFullDTO
    {
        return $this->userFullDTOResult;
    }

    public function exists(string $username): bool
    {
        return $this->existsResult;
    }

    public function isUsernameValid(string $username): bool
    {
        return $this->usernameValidResult;
    }

    public function save(UserCreateDTO $userData): bool
    {
        return $this->updateResult;
    }

    public function getLoginDTO(string $username): ?UserLoginDTO
    {
        return $this->loginDTOResult;
    }

    public function getRoleForUsername(string $username): ?UserRole
    {
        return $this->roleResult;
    }

    public function setUsernameValidResult(bool $usernameValidResult): void
    {
        $this->usernameValidResult = $usernameValidResult;
    }

    public function setExistsResult(bool $existsResult): void
    {
        $this->existsResult = $existsResult;
    }

    public function clearAll(): bool
    {
        return true;
    }

    public function getFullNameForUsername(string $username): ?FullNameDTO
    {
        return $this->fullNameResult;
    }

    public function isPublicIdValid(string $uuid): bool
    {
        return in_array($uuid, array_keys($this->users), true);
    }

    public function setPublicId(string $username, string $uuid): void
    {
        $this->publicIds[$username] = $uuid;
    }

    public function setUser(string $uuid, UserFullDTO $user): void
    {
        $this->users[$uuid] = $user;
    }

    public function getPublicIdForUsername(string $username): ?string
    {
        return $this->publicIds[$username] ?? null;
    }

    public function getByPublicId(string $uuid): UserFullDTO
    {
        return $this->users[$uuid];
    }

    public function getVirtualByPublicId(string $uuid): UserFullDTO
    {
        return $this->users[$uuid];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return [];
    }

    public function getUsernameForPublicId(string $publicId): ?string
    {
        return $this->users[$publicId]->username ?? null;
    }

    public function setUsername(string $target, string $new): bool
    {
        return $this->updateResult;
    }

    public function setFullName(string $target, FullNameDTO $new): bool
    {
        return $this->updateResult;
    }

    public function getPublicIds(): array
    {
        return $this->publicIds;
    }

    public function getVirtual(): array
    {
        return [];
    }

    public function addAdmin(string $username): bool
    {
        return $this->updateResult;
    }

    public function addTeacher(string $username, string ...$topics): bool
    {
        return $this->updateResult;
    }

    public function addStudent(string $username, string ...$topics): bool
    {
        return $this->updateResult;
    }

    public function revokeAdmin(string $username): bool
    {
        return $this->updateResult;
    }

    public function getAdmins(): array
    {
        return [];
    }

    public function getVirtualAdmins(): array
    {
        return [];
    }

    public function replaceTeacher(string $old, string $new): bool
    {
        return $this->updateResult;
    }

    public function softDelete(string $username): bool
    {
        return $this->updateResult;
    }

    public function hardDelete(string $username): bool
    {
        return $this->updateResult;
    }

    /**
     * @param string $username
     * @param string $email
     * @return bool
     */
    public function setEmail(string $username, string $email): bool
    {
        return $this->updateResult;
    }

    /**
     * @param string $username
     * @return bool
     */
    public function removeEmail(string $username): bool
    {
        return $this->updateResult;
    }

    public function getEmail(string $username): ?string
    {
        return $this->emailResult;
    }

    public function whitelist(string $username, FullNameDTO $fullName): bool
    {
        return $this->updateResult;
    }
}
