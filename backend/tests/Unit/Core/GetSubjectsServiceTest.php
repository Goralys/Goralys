<?php

namespace Goralys\Tests\Unit\Core;

use DateMalformedStringException;
use DateTime;
use Goralys\Core\Subjects\Services\GetSubjectsService;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserFullDTO;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use Goralys\Shared\Exception\GoralysRuntimeException;
use Goralys\Shared\User\Data\FullNameDTO;
use Goralys\Tests\Fakes\FakeGoralysLogger;
use Goralys\Tests\Fakes\FakeSubjectsRepository;
use Goralys\Tests\Fakes\FakeUserRepository;
use mysqli_result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetSubjectsServiceTest extends TestCase
{
    private FakeGoralysLogger $logger;
    private FakeSubjectsRepository $repo;
    private FakeUserRepository $userRepo;
    private UsernameFormatterService $formatter;
    private UsernameManager $usernameManager;
    private GetSubjectsService $service;
    private mysqli_result&MockObject $mysqliResult;

    /**
     * @throws DateMalformedStringException|GoralysRuntimeException
     */
    public function testGetAllSubjects()
    {
        $subjects = [
            [
                'teachers'        => 'j.doe1',
                'student'         => 'e.doe3',
                'topic'           => 'Maths',
                'subject'         => 'Étude des fonctions',
                'last_rejected'   => null,
                'subject_status'  => 3,
                'draftPath'       => "/path/to/draft",
                'last_updated_at' => '2026-03-29 10:15:00',
                'is_interdisciplinary' => true,
            ],
            [
                'teachers'        => 'm.smith2',
                'student'         => 'e.doe3',
                'topic'           => 'Physique',
                'subject'         => 'Ondes et interférences',
                'last_rejected'   => null,
                'subject_status'  => 1,
                'draftPath'       => null,
                'last_updated_at' => '2026-03-29 10:15:00',
                'is_interdisciplinary' => false,
            ],
            [
                'teachers'        => 'j.doe1',
                'student'         => 'l.dupont4',
                'topic'           => 'Informatique',
                'comment'         => "foo",
                'subject'         => 'Algorithmes de tri',
                'last_rejected'   => 'Algorithmes de tri',
                'subject_status'  => 2,
                'draftPath'       => null,
                'last_updated_at' => '2026-03-29 10:15:00',
                'is_interdisciplinary' => false,
            ],
            [
                'teachers'        => 'm.smith2',
                'student'         => 'l.dupont4',
                'topic'           => 'Sciences',
                'subject'         => 'Intelligence artificielle',
                'last_rejected'   => null,
                'subject_status'  => 3,
                'draftPath'       => null,
                'last_updated_at' => '2026-03-29 10:15:00',
                'is_interdisciplinary' => false,
            ],
            null,
        ];

        $expected = [
            [
                'student'        => 'DOE E.',
                'subject'        => 'Étude des fonctions',
                'status'         => 'approved',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Maths',
                'teacher'        => 'DOE J.',
                'hasDraft'       => false,
                'interdisciplinary' => true,
            ],
            [
                'student'        => 'DOE E.',
                'subject'        => 'Ondes et interférences',
                'status'         => 'submitted',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Physique',
                'teacher'        => 'SMITH M.',
                'hasDraft'       => false,
                'interdisciplinary' => false,
            ],
            [
                'student'        => 'DUPONT L.',
                'subject'        => 'Algorithmes de tri',
                'status'         => 'rejected',
                'comment'        => "foo",
                'lastRejected'   => 'Algorithmes de tri',
                'topic'          => 'Informatique',
                'teacher'        => "DOE J.",
                'hasDraft'       => false,
                'interdisciplinary' => false,
            ],
            [
                'student'        => 'DUPONT L.',
                'subject'        => 'Intelligence artificielle',
                'status'         => 'approved',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Sciences',
                'teacher'        => 'SMITH M.',
                'hasDraft'       => false,
                'interdisciplinary' => false,
            ],
        ];

        $this->mysqliResult->expects($this->atLeastOnce())
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(...$subjects);
        $this->repo->setQueryResult($this->mysqliResult);

        // Preparing the data
        $actual = json_encode($this->service->getAllSubjects(), JSON_UNESCAPED_UNICODE)
            |> (fn($x) => json_decode($x, true))
            |> $this->stripTokens(...);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws DateMalformedStringException|GoralysRuntimeException
     */
    public function testGetStudentSubjects()
    {
        $subjects = [
            [
                'teachers'        => 'j.doe1',
                'student'         => 'e.doe3',
                'topic'           => 'Maths',
                'subject'         => 'Étude des fonctions',
                'last_rejected'   => null,
                'subject_status'  => 3,
                'draftPath'       => "/path/to/draft",
                'last_updated_at' => '2026-03-26 11:15:00',
                'is_interdisciplinary' => false,
            ],
            [
                'teachers'        => 'm.smith2',
                'student'         => 'e.doe3',
                'topic'           => 'Physique',
                'subject'         => 'Ondes et interférences',
                'last_rejected'   => null,
                'subject_status'  => 1,
                'draftPath'       => null,
                'last_updated_at' => '2026-03-26 11:15:00',
                'is_interdisciplinary' => true,
            ],
            null,
        ];

        $expected = [
            [
                'student'        => 'DOE E.',
                'subject'        => 'Étude des fonctions',
                'status'         => 'approved',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Maths',
                'teacher'        => 'DOE J.',
                'hasDraft'       => false,
                'interdisciplinary' => false,
            ],
            [
                'student'        => 'DOE E.',
                'subject'        => 'Ondes et interférences',
                'status'         => 'submitted',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Physique',
                'teacher'        => 'SMITH M.',
                'hasDraft'       => false,
                'interdisciplinary' => true,
            ],
        ];

        $this->mysqliResult->expects($this->atLeastOnce())
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(...$subjects);
        $this->repo->setQueryResult($this->mysqliResult);

        // Preparing the data
        $actual = $this->stripTokens(json_decode(json_encode(
            $this->service->getStudentSubjects("e.doe3"),
            JSON_UNESCAPED_UNICODE,
        ), true));

        self::assertSame($expected, $actual);
    }

    private function stripTokens(array $data): array
    {
        return array_map(function ($s) {
            unset($s['studentToken'], $s['teacherToken']);
            return $s;
        }, $data);
    }

    /**
     * @throws DateMalformedStringException|GoralysRuntimeException
     */
    public function testGetTeacherSubjects()
    {
        $subjects = [
            [
                'teachers'        => 'j.doe1',
                'student'         => 'e.doe3',
                'topic'           => 'Maths',
                'subject'         => 'Étude des fonctions',
                'last_rejected'   => null,
                'subject_status'  => 3,
                'draftPath'       => "/path/to/draft",
                'last_updated_at' => '2026-02-26 11:45:00',
                'is_interdisciplinary' => false,
            ],
            [
                'teachers'        => 'j.doe1',
                'student'         => 'l.dupont4',
                'topic'           => 'Informatique',
                'comment'         => "foo",
                'subject'         => 'Algorithmes de tri',
                'last_rejected'   => 'Algorithmes de tri',
                'subject_status'  => 2,
                'draftPath'       => null,
                'last_updated_at' => '2026-03-26 11:15:00',
                'is_interdisciplinary' => true,
            ],
            null,
        ];

        $expected = [
            [
                'student'        => 'DOE E.',
                'subject'        => 'Étude des fonctions',
                'status'         => 'approved',
                'comment'        => "",
                'lastRejected'   => "",
                'topic'          => 'Maths',
                'teacher'        => 'DOE J.',
                'hasDraft'       => true,
                'interdisciplinary' => false,
            ],
            [
                'student'        => 'DUPONT L.',
                'subject'        => 'Algorithmes de tri',
                'status'         => 'rejected',
                'comment'        => "foo",
                'lastRejected'   => 'Algorithmes de tri',
                'topic'          => 'Informatique',
                'teacher'        => "DOE J.",
                'hasDraft'       => false,
                'interdisciplinary' => true,
            ],
        ];

        $this->mysqliResult->expects($this->atLeastOnce())
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(...$subjects);
        $this->repo->setQueryResult($this->mysqliResult);

        // Preparing the data
        $actual = $this->stripTokens(json_decode(json_encode(
            $this->service->getTeacherSubjects("j.doe1"),
            JSON_UNESCAPED_UNICODE,
        ), true));

        self::assertSame($expected, $actual);
    }

    private function seedUsers(): void
    {
        $this->userRepo->setPublicId("j.doe1", "u1");
        $this->userRepo->setUser("u1", new UserFullDTO(
            id: 1,
            username: "j.doe1",
            publicId: "u1",
            role: UserRole::TEACHER,
            fullName: new FullNameDTO("J.", "DOE"),
            email: "jhon.doe@exemplemail.com",
            createdAt: new DateTime(),
        ));

        $this->userRepo->setPublicId("m.smith2", "u2");
        $this->userRepo->setUser("u2", new UserFullDTO(
            id: 2,
            username: "m.smith2",
            publicId: "u2",
            role: UserRole::TEACHER,
            fullName: new FullNameDTO("M.", "SMITH"),
            email: "merry.smith@exemplemail.com",
            createdAt: new DateTime(),
        ));

        $this->userRepo->setPublicId("e.doe3", "u3");
        $this->userRepo->setUser("u3", new UserFullDTO(
            id: 3,
            username: "e.doe3",
            publicId: "u3",
            role: UserRole::STUDENT,
            fullName: new FullNameDTO("E.", "DOE"),
            email: "emma.doe@exemplemail.com",
            createdAt: new DateTime(),
        ));

        $this->userRepo->setPublicId("l.dupont4", "u4");
        $this->userRepo->setUser("u4", new UserFullDTO(
            id: 4,
            username: "l.dupont4",
            publicId: "u4",
            role: UserRole::STUDENT,
            fullName: new FullNameDTO("L.", "DUPONT"),
            email: "laurent.dupont@exemplemail.com",
            createdAt: new DateTime(),
        ));
    }

    protected function setUp(): void
    {
        $this->logger = new FakeGoralysLogger();
        $this->repo = new FakeSubjectsRepository();
        $this->userRepo = new FakeUserRepository();
        $this->formatter = new UsernameFormatterService();
        $this->usernameManager = new UsernameManager($this->userRepo);
        $this->mysqliResult = $this->createMock(mysqli_result::class);

        $this->service = new GetSubjectsService(
            $this->logger,
            $this->repo,
            $this->formatter,
            $this->usernameManager,
        );

        $this->seedUsers();
    }

    protected function tearDown(): void
    {
        unset($this->logger);
        unset($this->repo);
        unset($this->userRepo);
        unset($this->formatter);
        unset($this->usernameManager);
        unset($this->mysqliResult);
        unset($this->service);
    }
}
