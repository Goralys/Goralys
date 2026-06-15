<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Services\CreateUserService;
use Goralys\Shared\User\Data\FullNameDTO;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class CreateUserServiceTest extends TestCase
{
    private FakeUserRepository $repo;
    private CreateUserService $service;

    public function testCreateUserInvalidRole()
    {
        self::assertFalse($this->service->createUser(new UserCreateDTO(
            "j.doe1",
            new FullNameDTO("John", "Doe"),
            "foo",
            UserRole::UNKNOWN,
        )));
    }

    public function testCreateUserFails()
    {
        $this->repo->setUpdateResult(false);

        self::assertFalse($this->service->createUser(new UserCreateDTO(
            "j.doe1",
            new FullNameDTO("John", "Doe"),
            "foo",
            UserRole::STUDENT,
        )));
    }

    public function testCreateUserWorks()
    {
        $this->repo->setUpdateResult(true);

        self::assertTrue($this->service->createUser(new UserCreateDTO(
            "j.doe1",
            new FullNameDTO("John", "Doe"),
            "foo",
            UserRole::STUDENT,
        )));
    }

    public function testCreateUserWithTeacherRole(): void
    {
        $this->repo->setUpdateResult(true);

        self::assertTrue($this->service->createUser(new UserCreateDTO(
            "a.teacher1",
            new FullNameDTO("Alice", "Teacher"),
            "foo",
            UserRole::TEACHER,
        )));
    }

    public function testCreateUserWithAdminRole(): void
    {
        $this->repo->setUpdateResult(true);

        self::assertTrue($this->service->createUser(new UserCreateDTO(
            "a.admin1",
            new FullNameDTO("Alice", "Admin"),
            "foo",
            UserRole::ADMIN,
        )));
    }

    protected function setUp(): void
    {
        $this->repo = new FakeUserRepository();

        $this->service = new CreateUserService(
            $this->repo,
        );
    }

    protected function tearDown(): void
    {
        unset($this->repo);
        unset($this->service);
    }
}
