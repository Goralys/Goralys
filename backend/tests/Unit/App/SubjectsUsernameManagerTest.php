<?php

namespace Goralys\Tests\Unit\App;

use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class SubjectsUsernameManagerTest extends TestCase
{
    private UsernameManager $service;
    private FakeUserRepository $repo;

    public function testCreateReturnsNonEmptyToken(): void
    {
        $this->repo->setPublicId("j.doe", "uuid-1");

        $token = $this->service->resolve("j.doe");

        self::assertNotEmpty($token);
        self::assertSame("uuid-1", $token);
    }

    public function testCreateDifferentUsernamesGetDifferentTokens(): void
    {
        $this->repo->setPublicId("j.doe", "uuid-1");
        $this->repo->setPublicId("a.smith", "uuid-2");

        $token1 = $this->service->resolve("j.doe");
        $token2 = $this->service->resolve("a.smith");

        self::assertNotSame($token1, $token2);
    }

    public function testGetReturnsUsername(): void
    {
        $this->repo->setUser(
            "uuid-1",
            new UserFullDTO(1, "e.martin", UserRole::STUDENT, "Emma Martin", "emma.martin@exemplemail.com"),
        );

        $result = $this->service->get("uuid-1");

        self::assertSame("e.martin", $result);
    }

    public function testCreateAndGetConsistency(): void
    {
        $this->repo->setPublicId("j.doe", "uuid-1");
        $this->repo->setUser(
            "uuid-1",
            new UserFullDTO(1, "j.doe", UserRole::STUDENT, "John Doe", "jhon.doe@exemplemail.com"),
        );

        $token = $this->service->resolve("j.doe");
        $result = $this->service->get($token);

        self::assertSame("j.doe", $result);
    }

    public function testMultipleUsersAllRetrievable(): void
    {
        $users = [
            "j.doe"   => ["uuid-1", new UserFullDTO(
                1,
                "j.doe",
                UserRole::STUDENT,
                "John Doe",
                "jhon.doe@exemplemail.com"
            )],
            "a.smith" => ["uuid-2", new UserFullDTO(
                2,
                "a.smith",
                UserRole::TEACHER,
                "Alice Smith",
                "alice.smith@exemplemail.com"
            )],
            "e.martin" => ["uuid-3", new UserFullDTO(
                3,
                "e.martin",
                UserRole::STUDENT,
                "Emma Martin",
                "emma.martin@exemplemail.com"
            )],
        ];

        foreach ($users as $username => [$uuid, $dto]) {
            $this->repo->setPublicId($username, $uuid);
            $this->repo->setUser($uuid, $dto);
        }

        foreach ($users as $username => [$uuid, $dto]) {
            self::assertSame($uuid, $this->service->resolve($username));
            self::assertSame($username, $this->service->get($uuid));
        }
    }

    public function testGetThrowsForInvalidPublicId(): void
    {
        $this->expectException(GoralysRuntimeException::class);

        $this->service->get("invalid-uuid");
    }

    protected function setUp(): void
    {
        $this->repo = new FakeUserRepository();
        $this->service = new UsernameManager($this->repo);
    }

    protected function tearDown(): void
    {
        unset($this->service);
        unset($this->repo);
    }
}
