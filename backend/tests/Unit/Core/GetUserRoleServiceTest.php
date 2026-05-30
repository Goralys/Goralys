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
        $this->repo->setGetResult(null);

        try {
            $this->service->getRoleByUsername("j.doe1");
        } catch (UserNotFoundException $e) {
            self::assertEquals("No such user : j.doe1", $e->getMessage());
        }
    }

    /**
     * @throws UserNotFoundException
     */
    public function testGetRoleByUsernameRoleUnknown()
    {
        $this->repo->setGetResult(UserRole::UNKNOWN);
        self::assertEquals(UserRole::UNKNOWN, $this->service->getRoleByUsername("j.doe1"));
    }

    /**
     * @throws UserNotFoundException
     */
    public function testGetRoleByUsernameWorks()
    {
        $this->repo->setGetResult(UserRole::STUDENT);
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
