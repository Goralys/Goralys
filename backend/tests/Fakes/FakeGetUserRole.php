<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Interfaces\GetUserRoleInterface;

class FakeGetUserRole implements GetUserRoleInterface
{
    private UserRole $role = UserRole::STUDENT;

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function getRoleByUsername(string $username): UserRole
    {
        return $this->role;
    }
}
