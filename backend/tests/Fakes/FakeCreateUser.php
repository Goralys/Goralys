<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Interfaces\CreateUserInterface;

class FakeCreateUser implements CreateUserInterface
{
    public bool $success = true;

    public function createUser(UserCreateDTO $userData): bool
    {
        return $this->success;
    }
}
