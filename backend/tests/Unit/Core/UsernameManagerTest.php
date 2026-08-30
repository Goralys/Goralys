<?php

namespace Goralys\Tests\Unit\Core;

use DateTime;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\User\Data\FullNameDTO;
use Goralys\Tests\Fakes\FakeUserRepository;
use PHPUnit\Framework\TestCase;

class UsernameManagerTest extends TestCase
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
            new UserFullDTO(
                id: 1,
                username: "e.martin",
                publicId: "uuid-1",
                role: UserRole::STUDENT,
                fullName: new FullNameDTO("Emma", "Martin"),
                email: "emma.martin@exemplemail.com",
                createdAt: new DateTime(),
            ),
        );

        $result = $this->service->get("uuid-1");

        self::assertSame("e.martin", $result);
    }

    public function testCreateAndGetConsistency(): void
    {
        $this->repo->setPublicId("j.doe", "uuid-1");
        $this->repo->setUser(
            "uuid-1",
            new UserFullDTO(
                id: 1,
                username: "j.doe",
                publicId: "uuid-1",
                role: UserRole::STUDENT,
                fullName: new FullNameDTO("John", "Doe"),
                email: "jhon.doe@exemplemail.com",
                createdAt: new DateTime(),
            ),
        );

        $token = $this->service->resolve("j.doe");
        $result = $this->service->get($token);

        self::assertSame("j.doe", $result);
    }

    /**
     * @throws GoralysRuntimeException
     */
    public function testMultipleUsersAllRetrievable(): void
    {
        $users = [
            "j.doe"   => ["uuid-1", new UserFullDTO(
                id: 1,
                username: "j.doe",
                publicId: "uuid-1",
                role: UserRole::STUDENT,
                fullName: new FullNameDTO("John", "Doe"),
                email: "jhon.doe@exemplemail.com",
                createdAt: new DateTime(),
            )],
            "a.smith" => ["uuid-2", new UserFullDTO(
                id: 2,
                username: "a.smith",
                publicId: "uuid-2",
                role: UserRole::TEACHER,
                fullName: new FullNameDTO("Alice", "Smith"),
                email: "alice.smith@exemplemail.com",
                createdAt: new DateTime(),
            )],
            "e.martin" => ["uuid-3", new UserFullDTO(
                id: 3,
                username: "e.martin",
                publicId: "uuid-3",
                role: UserRole::STUDENT,
                fullName: new FullNameDTO("Emma", "Martin"),
                email: "emma.martin@exemplemail.com",
                createdAt: new DateTime(),
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
