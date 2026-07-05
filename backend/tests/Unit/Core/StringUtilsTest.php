<?php

namespace Goralys\Tests\Unit\Core;

use Goralys\Shared\Lib\String\StringCase;
use Goralys\Shared\Lib\String\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    private StringUtils $utils;

    public function testSanitizeTrimsWhitespace(): void
    {
        self::assertSame(
            "hello",
            $this->utils->sanitize("  hello  "),
            "Expected leading and trailing whitespace to be trimmed",
        );
    }

    public function testSanitizeNoCase(): void
    {
        self::assertSame(
            "Hello World",
            $this->utils->sanitize("Hello World"),
            "Expected string to remain unchanged with StringCase::NONE (default)",
        );
    }

    public function testSanitizeLowerCase(): void
    {
        self::assertSame(
            "hello world",
            $this->utils->sanitize("Hello World", StringCase::LOWER),
            "Expected string to be lowercased with StringCase::LOWER",
        );
    }

    public function testSanitizeUpperCase(): void
    {
        self::assertSame(
            "HELLO WORLD",
            $this->utils->sanitize("Hello World", StringCase::UPPER),
            "Expected string to be uppercased with StringCase::UPPER",
        );
    }

    public function testSanitizeEmptyString(): void
    {
        self::assertSame(
            "",
            $this->utils->sanitize(""),
            "Expected empty string to remain empty",
        );
    }

    public function testSanitizePlainAsciiUnchanged(): void
    {
        self::assertSame(
            "abc ABC 123",
            $this->utils->sanitize("abc ABC 123"),
            "Expected plain ASCII string to remain unchanged",
        );
    }

    protected function setUp(): void
    {
        $this->utils = new StringUtils();
    }

    protected function tearDown(): void
    {
        unset($this->utils);
    }
}
