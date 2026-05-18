<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Router;

use Closure;
use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\CSRFMiddleware;
use Goralys\App\HTTP\Middleware\DbMiddleware;
use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Middleware\RoleMiddleware;
use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\Router\Data\Route;
use Goralys\App\Router\Options\InputOptions;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Shared\Exception\Request\InvalidInputException;

/**
 * The HTTP router for the application.
 * Registers routes per HTTP method and dispatches incoming requests through
 * a middleware pipeline before invoking the route handler.
 */
final class GoralysRouter
{
    private GoralysKernel $kernel; // router should be the only class with this dependency.
    /** @var array<string, array<string, Route>> */
    private array $routes = [
        'POST' => [],
        'GET' => [],
        'PATCH' => [],
        'DELETE' => [],
        'BREW' => [],
        'WHEN' => [],
    ];
    /** @var array<string, class-string<MiddlewareInterface>>  */
    private array $middlewaresMap = [
        'auth' => AuthMiddleware::class,
        'toast' => ToastMiddleware::class,
        'role' => RoleMiddleware::class,
        'rate-limit' => RateLimitMiddleware::class,
        'csrf' => CSRFMiddleware::class,
        'db' => DbMiddleware::class,
    ];

    /**
     * @param GoralysKernel $kernel The application kernel (sole owner of this dependency).
     */
    public function __construct(GoralysKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Registers a route for the given HTTP method.
     * @param string $method The HTTP method (e.g., 'POST', 'GET').
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function add(string $method, string $route, Closure $handler, array ...$options): Route
    {
        return $this->routes[$method][$route] = new Route(
            $route,
            $method,
            $handler,
            empty($options) ? [] : array_merge_recursive(...array_values($options)),
        );
    }

    /**
     * Registers a POST route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function post(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('POST', $route, $handler, ...$options);
    }

    /**
     * Registers a GET route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function get(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('GET', $route, $handler, ...$options);
    }

    /**
     * Registers an update (PATCH) route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function update(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('PATCH', $route, $handler, ...$options);
    }

    /**
     * Registers a PUT route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function put(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('PUT', $route, $handler, ...$options);
    }

    /**
     * Registers a DELETE route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function delete(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('DELETE', $route, $handler, ...$options);
    }

    /**
     * Registers a BREW route.
     * @param string $route The route path.
     * @param Closure $handler The route handler.
     * @param array ...$options Optional middleware and input option arrays.
     * @return Route The registered route.
     */
    public function brew(string $route, Closure $handler, array ...$options): Route
    {
        return $this->add('BREW', $route, $handler, ...$options);
    }

    /**
     * @param list<MiddlewareInterface> $middlewares
     * @param callable $destination
     * @return mixed
     */
    private function pipeline(array $middlewares, callable $destination): mixed
    {
        $p = array_reduce($middlewares, function ($next, $mw) {
            return function () use ($mw, $next) {
                return $mw->handle($this->kernel, $next);
            };
        }, $destination);

        return $p();
    }

    /**
     * Dispatches the request to the matching route, running its middleware pipeline first.
     * Responds with 404 if no route matches or 400 if input validation fails.
     * @param string $method The HTTP method of the incoming request.
     * @param string $uri The URI path of the incoming request.
     * @return mixed The value returned by the route handler.
     */
    public function dispatch(string $method, string $uri): mixed
    {
        $this->kernel->logger->debug(LoggerInitiator::APP, "DISPATCH: $method $uri");
        $path = trim($uri, "/");

        if (!array_key_exists($method, $this->routes) || !array_key_exists($path, $this->routes[$method])) {
            $this->kernel->logger->error(
                LoggerInitiator::APP,
                "Unknow route $path, known:\n" . print_r($this->routes, true),
            );
            $this->kernel->response(404)->http();
        }

        $route = $this->routes[$method][$path];
        $request = $this->kernel->request();
        $resolved = [];
        foreach ($route->middlewares as $middleware) {
            $class = $this->middlewaresMap[$middleware->name] ?? null;
            if ($class === null) {
                $this->kernel->logger->error(
                    LoggerInitiator::APP,
                    "Unknown middleware: " . $middleware->name,
                );
                continue;
            }
            $resolved[] = new $class($path, ...$middleware->params);
        }

        if (isset($route->options['input']) && is_array($route->options['input'])) {
            try {
                $request->validate($route->options['input']);
            } catch (InvalidInputException) {
                $this->kernel->deferredResponse(400)->toast( // Bad Request
                    ToastType::WARNING,
                    "Champs invalides",
                    $route->options['input'][InputOptions::FAIL_MESSAGE_KEY] ?? "Veuillez remplir tous les champs.",
                )
                        ->redirect($route->options['input'][InputOptions::FAIL_MESSAGE_KEY] ?? "/")
                        ->send();
            }
        }

        $dest = function () use ($request, $route) {
            $this->kernel->run(function () use ($route, $request) {
                return ($route->handler)($this->kernel, $request);
            });
        };

        return $this->pipeline($resolved, $dest);
    }
}
