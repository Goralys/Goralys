<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware\Interface;

use Goralys\App\Cache\Interfaces\Discoverable;
use Goralys\Kernel\GoralysKernel;

/**
 * Contract for all HTTP middleware in the Goralys pipeline.
 * Each middleware receives the kernel and a `$next` callable to pass control downstream.
 */
interface MiddlewareInterface extends Discoverable
{
    /**
     * @param string $route The matched route path.
     * @param mixed ...$params Optional parameters passed from the route definition.
     */
    public function __construct(string $route, mixed ...$params);

    /**
     * Executes the middleware logic and calls `$next` to continue the pipeline.
     * @param GoralysKernel $kernel The application kernel.
     * @param callable $next The next handler in the pipeline.
     * @return mixed The response produced by the pipeline.
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed;

    /**
     * Returns the name of the middleware.
     * @return string The name of the middelware.
     */
    public static function name(): string;
}
