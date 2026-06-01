<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\App\Router\Data\Middleware;
use Goralys\Core\User\Data\Enums\UserRole;

/**
 * Factory class providing pre-composed middleware stacks for common route patterns.
 */
final class MiddlewareSets
{
    /**
     * Middlewares for general subjects routes.
     * @param string $action The action/endpoint of the route.
     * @param UserRole $role The role required for this endpoint.
     * @param bool $strict If the role should be tested strictly or not.
     * @param bool $transaction If the endpoint uses DB transactions or not.
     * @param bool $update If the endpoint performs an update action or not.
     * @param ?string $rateLimit The rate limit id for this endpoint.
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function subjectsRoute(
        string $action,
        UserRole $role,
        bool $strict = true,
        bool $transaction = false,
        bool $update = false,
        ?string $rateLimit = null,
    ): array {
        return [
            new Middleware(...RateLimitMiddleware::for(
                $rateLimit ?: (
                        $role === UserRole::ADMIN
                            ? ($update ? "admin-panel" : "admin-fetch")
                            : ($update ? "subject-update" : $action)
                ),
                '/subject'
            )),
            new Middleware(...CSRFMiddleware::form($action, '/subject')),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...RoleMiddleware::require($role, $strict)),
            new Middleware(...($transaction ? DbMiddleware::transaction() : DbMiddleware::require())),
        ];
    }

    /**
     * Middlewares for general topics routes.
     * @param string $action The action/endpoint of the route.
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function topicsRoute(string $action): array
    {
        return [
            new Middleware(...RateLimitMiddleware::for($action, '/subject')),
            new Middleware(...CSRFMiddleware::form($action, '/subject')),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...RoleMiddleware::require(UserRole::ADMIN, true)),
            new Middleware(...DbMiddleware::transaction()),
        ];
    }

    /**
     * Middlewares for general admin panel routes.
     * @param string $action The action/endpoint of the route.
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function adminPanelRoute(
        string $action,
        string $redirect = "/admin/user",
        bool $fetch = false,
        ?string $rateLimit = null
    ): array {
        return [
            new Middleware(...RateLimitMiddleware::for(
                $rateLimit ?? ($fetch ? "admin-fetch" : "admin-panel"),
                $redirect
            )),
            new Middleware(...CSRFMiddleware::form($action, $redirect)),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...RoleMiddleware::require(UserRole::ADMIN, true)),
        ];
    }

    /**
     * Middlewares for general support routes
     * @param string $action The action to perform.
     * @param array{0: UserRole, 1: boolean} $roleConfig The {@see RoleMiddleware} configuration
     * in the form of a [role, strict] array where role is the required role and strict is whether to require this role
     * strictly (exact match) or lightly (at least).
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function supportRoute(string $action, array $roleConfig = [UserRole::ADMIN, true]): array
    {
        return [
            new Middleware(...RoleMiddleware::require(...$roleConfig)),
            new Middleware(...RateLimitMiddleware::for($action, "/")),
            new Middleware(...CSRFMiddleware::form($action, "/")),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...DbMiddleware::require()),
        ];
    }
}
