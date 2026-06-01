<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Kernel\GoralysKernel;

/**
 * Middleware that enforces a minimum role level before the route handler runs.
 * In strict mode the session role must match exactly; otherwise `isAtLeast` is used.
 */
final class RoleMiddleware implements MiddlewareInterface
{
    private UserRole $role;
    private bool $strict;

    /**
     * @param string $route The matched route path.
     * @param mixed ...$params Expects [$role, $strict].
     */
    public function __construct(string $route, ...$params)
    {
        $this->role = $params[0];
        $this->strict = $params[1] ?? false;
    }

    /**
     * Returns the middleware binding for the given minimum role.
     * @param UserRole $role The minimum required role.
     * @param bool $strict If true, the session role must match exactly.
     * @return array The middleware descriptor array.
     */
    public static function require(UserRole $role, bool $strict = false): array
    {
        return ['role', [$role, $strict]];
    }

    /**
     * Verifies the session user meets the required role, aborting on failure.
     * @param GoralysKernel $kernel The application kernel.
     * @param callable $next The next handler in the pipeline.
     * @return mixed
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed
    {
        $kernel->requireRole($this->role, $this->strict);
        return $next($kernel);
    }
}
