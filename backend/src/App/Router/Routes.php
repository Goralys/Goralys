<?php

namespace Goralys\App\Router;

use Closure;
use Goralys\App\Router\Data\Route;

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

final class Routes
{
    /** @var array<string, array<string, Route>> */
    private static array $ROUTES = [
            'POST' => [],
            'GET' => [],
            'PATCH' => [],
            'DELETE' => [],
            'BREW' => [],
    ];

    /**
     * Registers a POST route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function post(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('POST', $route, $handler, ...$options);
    }

    /**
     * Registers a route for the given HTTP method.
     * @param string $method The HTTP method (e.g., 'POST', 'GET').
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    private static function add(string $method, string $route, Closure $handler, array ...$options): Route
    {
        return self::$ROUTES[$method][$route] = new Route(
            $route,
            $method,
            $handler,
            empty($options) ? [] : array_merge_recursive(...array_values($options)),
        );
    }

    /**
     * Registers a GET route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function get(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('GET', $route, $handler, ...$options);
    }

    /**
     * Registers a PATCH route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function patch(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('PATCH', $route, $handler, ...$options);
    }

    /**
     * Registers a PUT route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function put(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('PUT', $route, $handler, ...$options);
    }

    /**
     * Registers a DELETE route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function delete(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('DELETE', $route, $handler, ...$options);
    }

    /**
     * Registers a BREW route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public static function brew(string $route, Closure $handler, array ...$options): Route
    {
        return self::add('BREW', $route, $handler, ...$options);
    }

    /**
     * Clears all registered routes. Intended for use in test setup/teardown
     * only — never called from route declaration files.
     * @return void
     */
    public function reset(): void
    {
        self::$ROUTES = array_map(static fn () => [], self::$ROUTES);
    }

    /**
     * Returns all the routes registered by the API.
     * @return Route[][]
     */
    public function getAll(): array
    {
        return self::$ROUTES;
    }
}
