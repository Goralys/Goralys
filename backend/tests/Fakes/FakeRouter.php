<?php

namespace Goralys\Tests\Fakes;

use Goralys\App\Router\Interfaces\RouterInterface;
use Goralys\Kernel\GoralysKernel;

final class FakeRouter implements RouterInterface
{
    private array $knownFormIds = [];

    public function __construct(?GoralysKernel $kernel = null)
    {
    }

    /**
     * @param string[] $formIds The form ids that should be considered known/valid.
     */
    public function setKnownFormIds(array $formIds): void
    {
        $this->knownFormIds = array_fill_keys($formIds, true);
    }

    public function dispatch(string $method, string $uri): mixed
    {
        return null;
    }

    public function getKnownFormIds(): array
    {
        return $this->knownFormIds;
    }
}
