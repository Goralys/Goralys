<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Services\LoginService;
use Goralys\Shared\Exception\User\UserNotFoundException;
use Goralys\Tests\Fakes\FakeGoralysLogger;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase
{
    private FakeGoralysLogger $logger;
    private FakeUserRepository $repo;
    private LoginService $service;

    public function testLoginNoUser()
    {
        $this->repo->setLoginDTOResult(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("No such user : j.doe1");

        $this->service->login(new UserLoginDTO("j.doe1", "foo"));
    }

    /**
     * @throws UserNotFoundException
     */
    public function testLoginInvalidPassword()
    {
        $this->repo->setLoginDTOResult(new UserLoginDTO("j.doe1", "bar"));
        self::assertFalse($this->service->login(new UserLoginDTO("j.doe1", "foo")));
    }

    /**
     * @throws UserNotFoundException
     */
    public function testLoginWorks()
    {
        $this->repo->setLoginDTOResult(new UserLoginDTO("j.doe1", password_hash("foo", PASSWORD_DEFAULT)));
        self::assertTrue($this->service->login(new UserLoginDTO("j.doe1", "foo")));
    }

    public function testCheckPasswordNoUser(): void
    {
        $this->repo->setLoginDTOResult(null);

        try {
            $this->service->checkPassword(new UserLoginDTO("j.doe1", "foo"));
            self::fail("Expected UserNotFoundException was not thrown");
        } catch (UserNotFoundException $e) {
            self::assertSame("No such user : j.doe1", $e->getMessage());
        }
    }

    /**
     * @throws UserNotFoundException
     */
    public function testCheckPasswordInvalidPassword(): void
    {
        $this->repo->setLoginDTOResult(new UserLoginDTO("j.doe1", password_hash("correct", PASSWORD_DEFAULT)));
        self::assertFalse($this->service->checkPassword(new UserLoginDTO("j.doe1", "wrong")));
    }

    /**
     * @throws UserNotFoundException
     */
    public function testCheckPasswordWorks(): void
    {
        $this->repo->setLoginDTOResult(new UserLoginDTO("j.doe1", password_hash("foo", PASSWORD_DEFAULT)));
        self::assertTrue($this->service->checkPassword(new UserLoginDTO("j.doe1", "foo")));
    }

    protected function setUp(): void
    {
        $this->logger = new FakeGoralysLogger();
        $this->repo = new FakeUserRepository();

        $this->service = new LoginService(
            $this->logger,
            $this->repo,
        );
    }

    protected function tearDown(): void
    {
        unset($this->logger);
        unset($this->repo);
        unset($this->service);
    }
}
