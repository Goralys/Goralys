<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Interfaces\AddEmailServiceInterface;

class FakeEmailAddService implements AddEmailServiceInterface
{
    private bool $result = false;

    public function __construct()
    {
    }

    public function setResult(bool $result): void
    {
        $this->result = $result;
    }

    public function addEmail(string $username, string $email): bool
    {
        return $this->result;
    }
}
