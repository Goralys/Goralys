<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;

class FakeUserRepository implements UserRepositoryInterface
{
    private mixed $updateResult = true;
    private mixed $getResult = null;
    private bool $existsResult = false;
    private bool $usernameValidResult = false;
    private array $publicIds = [];
    private array $users = [];

    /**
     * Set the result for update/save operations.
     * @param mixed $updateResult
     */
    public function setUpdateResult(mixed $updateResult): void
    {
        $this->updateResult = $updateResult;
    }

    /**
     * Set the result for get/find operations.
     * @param mixed $getResult
     */
    public function setGetResult(mixed $getResult): void
    {
        $this->getResult = $getResult;
    }

    public function getByUsername(string $username): UserFullDTO
    {
        return $this->getResult;
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
        return (bool) $this->updateResult;
    }

    public function getLoginDTO(string $username): ?UserLoginDTO
    {
        return $this->getResult;
    }

    public function getRoleForUsername(string $username): ?UserRole
    {
        return $this->getResult;
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

    public function getFullNameForUsername(string $username): ?string
    {
        return $this->getResult;
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

    /**
     * @return array
     */
    public function getAll(): array
    {
        return [];
    }

    public function getUsernameForPublicId(string $publicId): ?string
    {
        return isset($this->users[$publicId]) ? $this->users[$publicId]->username : null;
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
        return (bool) $this->updateResult;
    }

    public function revokeAdmin(string $username): bool
    {
        return (bool) $this->updateResult;
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
        return (bool) $this->updateResult;
    }

    public function softDelete(string $username): bool
    {
        return (bool) $this->updateResult;
    }

    public function hardDelete(string $username): bool
    {
        return (bool) $this->updateResult;
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
}
