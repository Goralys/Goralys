<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserRegisterDTO;
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
    private FakeEmailAddService $mailAdder;
    private RegisterService $service;

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
        unset($this->mailAdder);
        unset($this->service);
    }

    public function testRegisterInvalidUsername()
    {
        $this->validator->setCanRegister(false);
        $this->roleGetter->setRole(UserRole::STUDENT);
        $this->userCreator->setSuccess(true);

        $result = $this->service->register(
            new UserRegisterDTO(username: "j.doe1", password: "Str0ngP@ss!", email: "j.doe1@example.com"),
        );

        self::assertFalse($result);
        self::assertSame('ERROR', $this->logger->logs[0]['level']);
        self::assertSame(
            "Failed to register user with user name : j.doe1",
            $this->logger->logs[0]['message'],
        );
    }

    public function testRegisterCannotCreateAccount()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->setRole(UserRole::STUDENT);
        $this->userCreator->setSuccess(false);

        $result = $this->service->register(
            new UserRegisterDTO(username: "j.doe1", password: "Str0ngP@ss!", email: "j.doe1@example.com"),
        );

        self::assertFalse($result);
        self::assertSame('ERROR', $this->logger->logs[0]['level']);
        self::assertSame(
            "Failed to create user with user name : j.doe1",
            $this->logger->logs[0]['message'],
        );
    }

    public function testRegisterWorks()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->setRole(UserRole::STUDENT);
        $this->userCreator->setSuccess(true);
        $this->mailAdder->setResult(true);

        $result = $this->service->register(
            new UserRegisterDTO(username: "j.doe1", password: "Str0ngP@ss!", email: "j.doe1@example.com"),
        );

        self::assertTrue($result);
        self::assertTrue($this->mailAdder->wasCalled());
        self::assertSame('INFO', $this->logger->logs[0]['level']);
        self::assertSame(
            "Successfully registered a new user with username : j.doe1(student)",
            $this->logger->logs[0]['message'],
        );
    }

    public function testRegisterWithTeacherRole()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->setRole(UserRole::TEACHER);
        $this->userCreator->setSuccess(true);
        $this->mailAdder->setResult(true);

        $result = $this->service->register(
            new UserRegisterDTO(username: "a.teacher1", password: "Str0ngP@ss!", email: "a.teacher1@example.com"),
        );

        self::assertTrue($result);
        self::assertSame(
            "Successfully registered a new user with username : a.teacher1(teacher)",
            $this->logger->logs[0]['message'],
        );
    }

    public function testRegisterDoesNotCallEmailAdderWhenNoEmailProvided()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->setRole(UserRole::STUDENT);
        $this->userCreator->setSuccess(true);

        $result = $this->service->register(
            new UserRegisterDTO(username: "j.doe1", password: "Str0ngP@ss!", email: null),
        );

        self::assertTrue($result);
        self::assertFalse($this->mailAdder->wasCalled(), "addEmail() should not be called when no email is provided");
    }

    public function testRegisterLogsErrorWhenEmailAddFailsButStillSucceeds()
    {
        $this->validator->setCanRegister(true);
        $this->roleGetter->setRole(UserRole::STUDENT);
        $this->userCreator->setSuccess(true);
        $this->mailAdder->setResult(false);

        $result = $this->service->register(
            new UserRegisterDTO(username: "j.doe1", password: "Str0ngP@ss!", email: "j.doe1@example.com"),
        );

        self::assertTrue($result, "A failed email add should not fail the whole registration");
        self::assertSame('ERROR', $this->logger->logs[0]['level']);
        self::assertSame(
            "Failed to add email j.doe1@example.com for user j.doe1",
            $this->logger->logs[0]['message'],
        );
        self::assertSame('INFO', $this->logger->logs[1]['level']);
    }
}
