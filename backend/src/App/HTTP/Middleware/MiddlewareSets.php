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
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function subjectsRoute(
        string $action,
        UserRole $role,
        bool $strict = true,
        bool $transaction = false,
    ): array {
        return [
            new Middleware(...RateLimitMiddleware::for($action, '/subject')),
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
    public static function adminPanelRoute(string $action, string $redirect = "/admin/user", bool $fetch = false): array
    {
        return [
            new Middleware(...RateLimitMiddleware::for($fetch ? "admin-fetch" : "admin-panel", $redirect)),
            new Middleware(...CSRFMiddleware::form($action, $redirect)),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...RoleMiddleware::require(UserRole::ADMIN, true)),
        ];
    }

    /**
     * Middlewares for general support routes
     * @param string $action The action to perform.
     * @return list<Middleware> The pre-composed middlewares list.
     */
    public static function supportRoute(string $action): array
    {
        return [
            new Middleware(...RoleMiddleware::require(UserRole::ADMIN, true)),
            new Middleware(...RateLimitMiddleware::for($action, "/")),
            new Middleware(...CSRFMiddleware::form($action, "/")),
            new Middleware(...AuthMiddleware::require()),
            new Middleware(...DbMiddleware::require()),
        ];
    }
}
