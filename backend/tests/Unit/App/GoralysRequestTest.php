<?php

namespace Goralys\Tests\Unit\App;

use Goralys\Shared\Exception\Request\InvalidInputException;
use Goralys\Tests\Fakes\FakeGoralysRequest;
use PHPUnit\Framework\TestCase;

final class GoralysRequestTest extends TestCase
{
    private FakeGoralysRequest $request;

    protected function setUp(): void
    {
        $this->request = new FakeGoralysRequest();
    }

    // get()

    public function testGetReturnsNullForMissingKey(): void
    {
        $this->assertNull($this->request->param('missing'));
    }

    public function testGetTrimsStringValues(): void
    {
        $this->request->setInput(['name' => '  John  ']);
        $this->assertSame('John', $this->request->param('name'));
    }

    public function testGetReturnsInt(): void
    {
        $this->request->setInput(['age' => 42]);
        $this->assertSame(42, $this->request->param('age'));
    }

    public function testGetReturnsFloat(): void
    {
        $this->request->setInput(['score' => 3.14]);
        $this->assertSame(3.14, $this->request->param('score'));
    }

    public function testGetReturnsBoolTrue(): void
    {
        $this->request->setInput(['active' => true]);
        $this->assertSame(true, $this->request->param('active'));
    }

    public function testGetReturnsBoolFalse(): void
    {
        $this->request->setInput(['active' => false]);
        $this->assertSame(false, $this->request->param('active'));
    }

    // validate() — happy path

    /**
     * @throws InvalidInputException
     */
    public function testValidateReturnsValidatedData(): void
    {
        $this->request->setInput(['username' => 'john']);
        $result = $this->request->validate(['username' => ['required']]);
        $this->assertSame(['username' => 'john'], $result);
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidatePassesWithMultipleFields(): void
    {
        $this->request->setInput(['username' => 'john', 'password' => 'secret']);
        $result = $this->request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);
        $this->assertSame(['username' => 'john', 'password' => 'secret'], $result);
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidatePassesMinConstraint(): void
    {
        $this->request->setInput(['password' => 'strongpass']);
        $result = $this->request->validate(['password' => ['required', 'min:6']]);
        $this->assertSame(['password' => 'strongpass'], $result);
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidateIncludesOptionalFieldWhenPresent(): void
    {
        $this->request->setInput(['note' => 'hello']);
        $result = $this->request->validate(['note' => []]);
        $this->assertSame(['note' => 'hello'], $result);
    }

    /**
     * @throws InvalidInputException
     */
    public function testValidateIncludesOptionalFieldAsNullWhenAbsent(): void
    {
        $this->request->setInput([]);
        $result = $this->request->validate(['note' => []]);
        $this->assertSame(['note' => null], $result);
    }

    // validate() — required failures

    public function testValidateThrowsWhenRequiredFieldMissing(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->request->setInput([]);
        $this->request->validate(['username' => ['required']]);
    }

    public function testValidateThrowsWhenRequiredFieldIsEmptyString(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->request->setInput(['username' => '   ']);
        $this->request->validate(['username' => ['required']]);
    }

    public function testValidateThrowsWhenRequiredFieldIsNull(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->request->setInput(['username' => null]);
        $this->request->validate(['username' => ['required']]);
    }

    // validate() — min failures

    public function testValidateThrowsWhenValueIsBelowMin(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->request->setInput(['password' => 'abc']);
        $this->request->validate(['password' => ['required', 'min:6']]);
    }
}
