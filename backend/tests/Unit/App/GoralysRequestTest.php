<?php

namespace Goralys\Tests\Unit\App;

use Goralys\App\HTTP\Request\GoralysRequest;
use Goralys\Shared\Exception\Request\InvalidInputException;
use PHPUnit\Framework\TestCase;

final class GoralysRequestTest extends TestCase
{
    private GoralysRequest $request;

    protected function setUp(): void
    {
        $this->request = new GoralysRequest();
    }

    protected function tearDown(): void
    {
        unset($_POST);
        unset($_GET);
        unset($_SERVER);
    }

    public function testReturnsNullForMissingParam()
    {
        $_POST = [];
        $this->setUp();
        self::assertSame(null, $this->request->param("missing"));
    }

    public function testTrimsParams()
    {
        $_POST = ["foo" => "              almost trimmed    "];
        $this->setUp();
        self::assertSame("almost trimmed", $this->request->param("foo"));
    }

    public function testReadsGetIfPostEmpty()
    {
        $_POST = [];
        $_GET = ["foo" => "bar"];
        $this->setUp();
        self::assertSame("bar", $this->request->param("foo"));
    }

    public function testGetOverwritesPost() // derived from merge order in the class
    {
        $_POST = ["foo" => "bar"];
        $_GET = ["foo" => "bar1"];
        $this->setUp();
        self::assertSame("bar1", $this->request->param("foo"));
    }

    public function testNonStringValuesRemains()
    {
        $_POST = ["foo" => true];
        $_GET = ["bar" => 3];
        $this->setUp();
        self::assertIsBool($this->request->param("foo"));
        self::assertSame(true, $this->request->param("foo"));
        self::assertIsInt($this->request->param("bar"));
        self::assertSame(3, $this->request->param("bar"));
    }

    public function testReturnsNullForMissingHeader()
    {
        $_SERVER = [];
        $this->setUp();
        self::assertSame(null, $this->request->header("missing"));
    }

    public function testHeaderWorks()
    {
        $_SERVER["HTTP_X_FOO"] = "bar";
        $this->setUp();
        self::assertSame("bar", $this->request->header("X-Foo"));
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidateRequired()
    {
        $_POST = ["foo" => "bar"];
        $this->setUp();
        self::assertSame(["foo" => "bar"], $this->request->validate(["foo" => ["required"]]));
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidateMin()
    {
        $_POST = ["foo" => "bar"];
        $this->setUp();
        self::assertSame(["foo" => "bar"], $this->request->validate(["foo" => ["min:3"]]));
    }

    public function testValidateThrowsForMissingRequired()
    {
        $_POST = ["foo1" => "bar"];
        $this->setUp();
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage("foo is required");
        $this->request->validate(["foo" => ["required"]]);
    }

    public function testValidateThrowsForNullRequired()
    {
        $_POST = ["foo" => null];
        $this->setUp();
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage("foo is required");
        $this->request->validate(["foo" => ["required"]]);
    }

    public function testValidateThrowsForEmptyRequired()
    {
        $_POST = ["foo" => "    "];
        $this->setUp();
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage("foo cannot be empty");
        $this->request->validate(["foo" => ["required"]]);
    }

    public function testValidateThrowsForLessThanMin()
    {
        $_POST = ["foo" => "bar"];
        $this->setUp();
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage("foo is too short (min 4)");
        $this->request->validate(["foo" => ["min:4"]]);
    }
}
