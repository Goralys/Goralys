<?php

namespace Goralys\App\Router\Interfaces;

use Goralys\Kernel\GoralysKernel;

interface RouterInterface
{
    public function __construct(GoralysKernel $kernel);

    /**
     * Dispatches the request to the matching route.
     * @param string $method The HTTP method of the incoming request.
     * @param string $uri The URI path of the incoming request.
     * @return mixed The value returned by the route handler.
     */
    public function dispatch(string $method, string $uri): mixed;

    /**
     * Gets the list of known form ids to validate CSRF tokens.
     * @return list<string> The known form ids.
     */
    public function getKnownFormIds(): array;
}
