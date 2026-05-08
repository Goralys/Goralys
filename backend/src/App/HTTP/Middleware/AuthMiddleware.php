<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

/**
 * Middleware that enforces authentication before a route handler is executed.
 * Supports a "weak" mode that only clears the session (user-related fields) on failure instead of destroying it.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    private string $endpoint;
    private array $options;

    /**
     * @param string $endpoint The matched route path.
     * @param mixed ...$params Optional mode flags (e.g., 'weak').
     */
    public function __construct(string $endpoint, mixed ...$params)
    {
        $this->endpoint = $endpoint;
        $this->options = $params;
    }

    /**
     * @param GoralysKernel $kernel
     * @param callable $next
     * @return mixed
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed
    {
        if (in_array('weak', $this->options)) {
            if (!$kernel->checkAuth()) {
                unset($_SESSION["current_username"]);
                unset($_SESSION["current_role"]);
                unset($_SESSION["current_id"]);
                unset($_SESSION["current_full_name"]);
                $kernel->response(401)->http(); // Unauthorized
            }

            return $next($kernel);
        }
        $kernel->requireAuth($this->endpoint);
        return $next($kernel);
    }

    /**
     * Returns the middleware binding for strict authentication (redirects on failure).
     * @return array The middleware descriptor array.
     */
    public static function require(): array
    {
        return ['auth'];
    }

    /**
     * Returns the middleware binding for weak authentication (clears session on failure, no redirect).
     * @return array The middleware descriptor array.
     */
    public static function weak(): array
    {
        return ['auth', ['weak']];
    }
}
