<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Router\Data;

use Closure;

/**
 * Represents a registered route, holding its path, HTTP method, handler, and middleware stack.
 */
final class Route
{
    /** @param list<Middleware> $middlewares */
    public function __construct(
        public readonly string $route,
        public readonly string $method,
        public readonly Closure $handler,
        public readonly array $options,
        public array $middlewares = [],
    ) {
    }

    /**
     * Appends single-named middleware (with optional parameters) to the route's stack.
     * @param string $name The middleware name as registered in the router's middleware map.
     * @param mixed ...$params Optional parameters to pass to the middleware.
     * @return self
     */
    public function middleware(string $name, mixed ...$params): self
    {
        $this->middlewares[] = new Middleware($name, ...$params);
        return $this;
    }

    /**
     * Appends one or more pre-built Middleware instances to the route's stack.
     * @param Middleware ...$middlewares The middleware objects to add.
     * @return self
     */
    public function middlewares(Middleware ...$middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }
}
