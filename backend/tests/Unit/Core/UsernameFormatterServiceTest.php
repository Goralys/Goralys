<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Core\Utils\User\Services\UsernameFormatterService;
use PHPUnit\Framework\TestCase;

class UsernameFormatterServiceTest extends TestCase
{
    private UsernameFormatterService $service;

    public function testFormatUsernameStandardFormat(): void
    {
        self::assertSame(
            "DOE J.",
            $this->service->formatUsername("j.doe"),
            "Expected 'j.doe' to be formatted as 'DOE J.'",
        );
    }

    public function testFormatUsernameWithTrailingDigit(): void
    {
        self::assertSame(
            "DOE J.",
            $this->service->formatUsername("j.doe1"),
            "Expected 'j.doe1' to be formatted as 'DOE J.'",
        );
    }

    public function testFormatUsernameWithMultipleTrailingDigits(): void
    {
        self::assertSame(
            "SMITH A.",
            $this->service->formatUsername("a.smith42"),
            "Expected 'a.smith42' to be formatted as 'SMITH A.'",
        );
    }

    public function testFormatUsernameUppercaseInput(): void
    {
        self::assertSame(
            "DOE J.",
            $this->service->formatUsername("J.DOE"),
            "Expected 'J.DOE' to be formatted as 'DOE J.'",
        );
    }

    public function testFormatUsernameInvalidFormatReturnsOriginal(): void
    {
        self::assertSame(
            "invalid_username",
            $this->service->formatUsername("invalid_username"),
            "Expected invalid username to be returned as-is",
        );
    }

    public function testFormatUsernameEmptyStringReturnsOriginal(): void
    {
        self::assertSame(
            "",
            $this->service->formatUsername(""),
            "Expected empty string to be returned as-is",
        );
    }

    public function testFormatUsernameNoDotReturnsOriginal(): void
    {
        self::assertSame(
            "doe",
            $this->service->formatUsername("doe"),
            "Expected username without dot to be returned as-is",
        );
    }

    protected function setUp(): void
    {
        $this->service = new UsernameFormatterService();
    }

    protected function tearDown(): void
    {
        unset($this->service);
    }
}
