<?php

namespace Goralys\Tests\Unit\Platform;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\GoralysLogger;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use PHPUnit\Framework\TestCase;

class GoralysLoggerTest extends TestCase
{
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = new GoralysLogger();
    }

    public function testInfoLogsCorrectly(): void
    {
        $this->logger->info(LoggerInitiator::APP, "Info message");
        $logs = $this->logger->store();
        self::assertCount(1, $logs);
        self::assertSame('INFO', $logs[0]['level']);
        self::assertSame(LoggerInitiator::APP->toString(), $logs[0]['initiator']);
        self::assertSame("Info message", $logs[0]['message']);
    }

    public function testDebugLogsCorrectly(): void
    {
        $this->logger->debug(LoggerInitiator::CORE, "Debug message");
        $logs = $this->logger->store();
        self::assertCount(1, $logs);
        self::assertSame('DEBUG', $logs[0]['level']);
    }

    public function testWarningLogsCorrectly(): void
    {
        $this->logger->warning(LoggerInitiator::PLATFORM, "Warning message");
        $logs = $this->logger->store();
        self::assertCount(1, $logs);
        self::assertSame('WARNING', $logs[0]['level']);
    }

    public function testErrorLogsCorrectly(): void
    {
        $this->logger->error(LoggerInitiator::KERNEL, "Error message");
        $logs = $this->logger->store();
        self::assertCount(1, $logs);
        self::assertSame('ERROR', $logs[0]['level']);
    }

    public function testFatalLogsCorrectly(): void
    {
        $this->logger->fatal(LoggerInitiator::APP, "Fatal message");
        $logs = $this->logger->store();
        self::assertCount(1, $logs);
        self::assertSame('FATAL', $logs[0]['level']);
    }

    public function testRotateDoesNotClearExistingLogs(): void
    {
        $this->logger->info(LoggerInitiator::APP, "Message");
        $this->logger->rotate();

        self::assertCount(1, $this->logger->store(), "Expected rotate() not to clear previously recorded logs");
        self::assertSame("Message", $this->logger->store()[0]['message']);
    }
}
