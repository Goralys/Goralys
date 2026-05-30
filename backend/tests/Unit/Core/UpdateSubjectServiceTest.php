<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\Subjects\Data\Enums\SubjectStatus;
use Goralys\Core\Subjects\Services\UpdateSubjectService;
use Goralys\Tests\Fakes\FakeGoralysLogger;
use Goralys\Tests\Fakes\FakeSubjectsRepository;
use PHPUnit\Framework\TestCase;

class UpdateSubjectServiceTest extends TestCase
{
    private FakeGoralysLogger $logger;
    private FakeSubjectsRepository $repo;
    private UpdateSubjectService $service;

    public function testUpdateSubjectStatus()
    {
        $this->repo->setUpdateResult(false);
        self::assertFalse($this->service->updateSubjectStatus(
            "j.doe1",
            "e.doe3",
            "Maths",
            SubjectStatus::APPROVED,
        ));

        $this->repo->setUpdateResult(true);
        self::assertTrue($this->service->updateSubjectStatus(
            "j.doe1",
            "e.doe3",
            "Maths",
            SubjectStatus::SUBMITTED,
        ));
    }

    public function testUpdateComment()
    {
        $this->repo->setUpdateResult(false);
        self::assertFalse($this->service->updateComment(
            "j.doe1",
            "e.doe3",
            "Maths",
            "foo",
        ));

        $this->repo->setUpdateResult(true);
        self::assertTrue($this->service->updateComment(
            "j.doe1",
            "e.doe3",
            "Maths",
            "bar",
        ));
    }

    public function testUpdateSubject()
    {
        $this->repo->setUpdateResult(false);
        self::assertFalse($this->service->updateSubject(
            "j.doe1",
            "e.doe3",
            "Maths",
            "foo",
            false,
        ));

        $this->repo->setUpdateResult(true);
        self::assertTrue($this->service->updateSubject(
            "j.doe1",
            "e.doe3",
            "Maths",
            "bar",
            true,
        ));
    }

    protected function setUp(): void
    {
        $this->logger = new FakeGoralysLogger();
        $this->repo = new FakeSubjectsRepository();

        $this->service = new UpdateSubjectService(
            $this->logger,
            $this->repo,
        );
    }

    protected function tearDown(): void
    {
        unset($this->logger);
        unset($this->repo);
        unset($this->mysqliResult);
        unset($this->service);
    }
}
