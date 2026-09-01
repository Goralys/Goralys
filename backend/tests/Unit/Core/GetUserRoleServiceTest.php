<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Services\GetUserRoleService;
use Goralys\Shared\Exception\User\UserNotFoundException;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class GetUserRoleServiceTest extends TestCase
{
    private FakeUserRepository $repo;
    private GetUserRoleService $service;

    public function testGetRoleByUsernameNoUser()
    {
        $this->repo->setRoleResult(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("No such user : j.doe1");

        $this->service->getRoleByUsername("j.doe1");
    }

    /**
     * @throws UserNotFoundException
     */
    public function testGetRoleByUsernameRoleUnknown()
    {
        $this->repo->setRoleResult(UserRole::UNKNOWN);
        self::assertEquals(UserRole::UNKNOWN, $this->service->getRoleByUsername("j.doe1"));
    }

    /**
     * @throws UserNotFoundException
     */
    public function testGetRoleByUsernameWorks()
    {
        $this->repo->setRoleResult(UserRole::STUDENT);
        self::assertEquals(UserRole::STUDENT, $this->service->getRoleByUsername("j.doe1"));
    }

    protected function setUp(): void
    {
        $this->repo = new FakeUserRepository();

        $this->service = new GetUserRoleService(
            $this->repo,
        );
    }

    protected function tearDown(): void
    {
        unset($this->repo);
        unset($this->service);
    }
}
