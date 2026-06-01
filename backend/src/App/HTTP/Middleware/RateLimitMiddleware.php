<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\Kernel\GoralysKernel;

/**
 * Middleware that enforces a rate limit for a named endpoint before the route handler runs.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private string $endpoint;
    private ?string $redirect;
    private ?string $message;

    /**
     * @param string $route The matched route path.
     * @param mixed ...$params Expects [$endpoint, $redirect, $message].
     */
    public function __construct(string $route, ...$params)
    {
        $this->endpoint = $params[0] ?? $route;
        $this->redirect = $params[1] ?? null;
        $this->message = $params[2] ?? null;
    }

    /**
     * Returns the middleware binding for the given endpoint's rate limit.
     * @param string $endpoint The named rate-limit rule to apply.
     * @param ?string $redirect The page to redirect to on rate limit exceeded (defaults to "/").
     * @param ?string $message A custom error message to display.
     * @return array The middleware descriptor array.
     */
    public static function for(string $endpoint, ?string $redirect = null, ?string $message = null): array
    {
        return ['rate-limit', [$endpoint, $redirect, $message]];
    }

    /**
     * Checks the rate limit for the configured endpoint, aborting the request if exceeded.
     * @param GoralysKernel $kernel The application kernel.
     * @param callable $next The next handler in the pipeline.
     * @return mixed
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed
    {
        $kernel->requireRateLimit(
            $this->endpoint,
            $this->redirect ?? "/",
            $this->message ?? "Trop de requêtes. Veuillez réessayer plus tard.",
        );

        return $next($kernel);
    }
}
