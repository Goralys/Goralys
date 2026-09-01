<?php

namespace Goralys\Tests\Fakes;

use Goralys\Core\User\Interfaces\AddEmailServiceInterface;

class FakeEmailAddService implements AddEmailServiceInterface
{
    private bool $result = false;
    private bool $wasCalled = false;

    public function setResult(bool $result): void
    {
        $this->result = $result;
    }

    public function wasCalled(): bool
    {
        return $this->wasCalled;
    }

    public function addEmail(string $username, string $email): bool
    {
        $this->wasCalled = true;
        return $this->result;
    }
}
