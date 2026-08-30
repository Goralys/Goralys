<?php

namespace Goralys\Tests\Unit\App;

use Goralys\App\Security\CSRF\Services\CSRFService;
use Goralys\Tests\Fakes\FakeGoralysLogger;
use Goralys\Tests\Fakes\FakeGoralysRequest;
use Goralys\Tests\Fakes\FakeRouter;
use PHPUnit\Framework\TestCase;

class CSRFServiceTest extends TestCase
{
    private CSRFService $service;
    private FakeRouter $router;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_SERVER = [];
        $this->router = new FakeRouter();
        $this->service = new CSRFService(new FakeGoralysLogger(), $this->router);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_SERVER = [];
        unset($this->service);
        unset($this->router);
    }

    /**
     * Builds a session-stored token entry in the shape expected by CSRFService.
     */
    private static function token(string $token, int $expiresInSeconds = 600): array
    {
        return ["token" => $token, "expires_at" => time() + $expiresInSeconds];
    }

    public function testCreateTokenForKnownFormId()
    {
        $this->router->setKnownFormIds(["foo"]);

        self::assertTrue($this->service->create("foo"));
        self::assertArrayHasKey(
            "foo",
            $_SESSION['csrf-tokens-table'],
            "Failed to create token for form 'foo'. Expected token to exist in session.",
        );
    }

    public function testCreateTokenFailsForUnknownFormId()
    {
        $this->router->setKnownFormIds([]);

        self::assertFalse($this->service->create("foo"));
        self::assertArrayNotHasKey(
            'csrf-tokens-table',
            $_SESSION,
            "Expected no token to be created for an unknown form id",
        );
    }

    public function testValidateSucceedsWithMatchingToken()
    {
        $request = new FakeGoralysRequest();
        $request->setInput(['csrf-token' => "foo-xy"]);
        $_SESSION['csrf-tokens-table']['bar'] = [self::token("foo-xy")];

        self::assertTrue(
            $this->service->validate("bar", $request),
            "Validation failed for form 'bar' with matching token 'foo-xy'",
        );
    }

    public function testValidateFailsWithNonMatchingToken()
    {
        $request = new FakeGoralysRequest();
        $request->setInput(['csrf-token' => "foo-xy"]);
        $_SESSION['csrf-tokens-table']['bar1'] = [self::token("foo-xyz"), self::token("foo-x")];

        self::assertFalse(
            $this->service->validate("bar1", $request),
            "Validation passed for form 'bar1' when it shouldn't have with token 'foo-xy'",
        );
    }

    public function testValidateFailsWhenExpired()
    {
        $request = new FakeGoralysRequest();
        $request->setInput(['csrf-token' => "foo-xy"]);
        $_SESSION['csrf-tokens-table']['bar'] = [self::token("foo-xy", -60)];

        self::assertFalse(
            $this->service->validate("bar", $request),
            "Validation passed for form 'bar' with an expired token",
        );
    }

    public function testValidateFailsWhenNoTokensExistForAnyForm()
    {
        $request = new FakeGoralysRequest();
        $request->setInput(['csrf-token' => 'some-token']);
        unset($_SESSION['csrf-tokens-table']);

        self::assertFalse(
            $this->service->validate("foo2", $request),
            "Validation passed for form 'foo2' when no token was provided",
        );
    }

    public function testValidateFailsWhenNoTokenProvidedInRequest()
    {
        $request = new FakeGoralysRequest();
        $request->setInput([]);
        $_SESSION['csrf-tokens-table']['bar3'] = [self::token("foo-xya")];

        self::assertFalse(
            $this->service->validate("bar3", $request),
            "Validation passed for form 'bar3' with no token provided in request",
        );
    }

    public function testGetForFormReturnsLastToken()
    {
        $_SESSION['csrf-tokens-table']['foo'] = [self::token("foo-xyz"), self::token("foo-xy")];

        self::assertSame(
            "foo-xy",
            $this->service->getForForm("foo"),
            "Failed to retrieve token for form 'foo' which should be 'foo-xy'",
        );
    }

    public function testGetForFormReturnsEmptyStringForUnknownForm()
    {
        $_SESSION['csrf-tokens-table']['foo'] = [self::token("foo-xyz"), self::token("foo-xy")];

        self::assertSame(
            "",
            $this->service->getForForm("bar"),
            "Retrieved token for non-existing form 'bar' which shouldn't have a token",
        );
    }

    public function testGetForFormReturnsEmptyStringAfterSessionTokensCleared()
    {
        $_SESSION['csrf-tokens-table']['foo'] = [self::token("foo-xyz"), self::token("foo-xy")];
        unset($_SESSION['csrf-tokens-table']);

        self::assertSame(
            "",
            $this->service->getForForm("foo"),
            "Retrieved token for form 'foo' after session tokens were cleared",
        );
    }
}
