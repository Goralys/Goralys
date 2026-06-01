<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Services\RegisterValidatorService;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class RegisterValidatorServiceTest extends TestCase
{
    private FakeUserRepository $repo;
    private RegisterValidatorService $service;

    public function testCanRegisterExistsAndInvalidUsername()
    {
        $this->repo->setUsernameValidResult(false);
        $this->repo->setExistsResult(true);
        self::assertFalse($this->service->canRegister(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testCanRegisterExists()
    {
        $this->repo->setUsernameValidResult(true);
        $this->repo->setExistsResult(true);
        self::assertFalse($this->service->canRegister(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testCanRegisterInvalidUsername()
    {
        $this->repo->setUsernameValidResult(false);
        $this->repo->setExistsResult(false);
        self::assertFalse($this->service->canRegister(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testCanRegisterWorks()
    {
        $this->repo->setUsernameValidResult(true);
        $this->repo->setExistsResult(false);
        self::assertTrue($this->service->canRegister(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    protected function setUp(): void
    {
        $this->repo = new FakeUserRepository();

        $this->service = new RegisterValidatorService(
            $this->repo,
        );
    }

    protected function tearDown(): void
    {
        unset($this->repo);
        unset($this->service);
    }
}
