<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Interfaces\AddEmailServiceInterface;
use Goralys\Core\User\Services\RegisterService;
use Goralys\Tests\Fakes\FakeCreateUser;
use Goralys\Tests\Fakes\FakeEmailAddService;
use Goralys\Tests\Fakes\FakeGetUserRole;
use Goralys\Tests\Fakes\FakeGoralysLogger;
use Goralys\Tests\Fakes\FakeRegisterValidatorService;
use PHPUnit\Framework\TestCase;

class RegisterServiceTest extends TestCase
{
    private FakeGoralysLogger $logger;
    private FakeRegisterValidatorService $validator;
    private FakeGetUserRole $roleGetter;
    private FakeCreateUser $userCreator;
    private RegisterService $service;
    private AddEmailServiceInterface $mailAdder;

    public function testRegisterInvalidUsername()
    {
        $this->validator->setCanRegister(false);
        $this->roleGetter->role = UserRole::STUDENT;
        $this->userCreator->success = true;
        self::assertFalse($this->service->register(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testRegisterInvalidRole()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->role = UserRole::UNKNOWN;
        $this->userCreator->success = false;
        self::assertFalse($this->service->register(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testRegisterCannotCreateAccount()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->role = UserRole::STUDENT;
        $this->userCreator->success = false;
        self::assertFalse($this->service->register(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    public function testRegisterWorks()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->role = UserRole::STUDENT;
        $this->userCreator->success = true;
        self::assertTrue($this->service->register(new UserRegisterDTO("j.doe1", "John Doe", "foo")));
    }

    protected function setUp(): void
    {
        $this->logger = new FakeGoralysLogger();
        $this->validator = new FakeRegisterValidatorService();
        $this->roleGetter = new FakeGetUserRole();
        $this->userCreator = new FakeCreateUser();
        $this->mailAdder = new FakeEmailAddService();

        $this->service = new RegisterService(
            $this->logger,
            $this->validator,
            $this->roleGetter,
            $this->userCreator,
            $this->mailAdder,
        );
    }

    protected function tearDown(): void
    {
        unset($this->logger);
        unset($this->validator);
        unset($this->roleGetter);
        unset($this->userCreator);
        unset($this->service);
        unset($this->mailAdder);
    }
}
