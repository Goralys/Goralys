<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Interfaces\CreateUserInterface;

class FakeCreateUser implements CreateUserInterface
{
    private bool $success = true;

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function createUser(UserCreateDTO $userData): bool
    {
        return $this->success;
    }
}
