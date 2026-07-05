<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

    namespace Goralys\App\HTTP\Middleware;

    use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
    use Goralys\App\Utils\Toast\Data\Enums\ToastType;
    use Goralys\Kernel\GoralysKernel;
    use Goralys\Shared\Config\GoralysConfig;

/**
 * Middleware that enforces authentication before a route handler is executed.
 * Supports a "weak" mode that only clears the user-related fields inside the session on failure
 * instead of destroying it completely.
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
     * Returns the middleware binding for strict authentication (redirects on failure).
     * @return array The middleware descriptor array.
     */
    public static function require(): array
    {
        return ['auth'];
    }

    /**
     * Returns the middleware binding for weak authentication (clears session on failure).
     * If the auth check fails, it triggers a client redirect to the login page as a side effect.
     * @return array The middleware descriptor array.
     */
    public static function weak(): array
    {
        return ['auth', ['weak']];
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
                foreach (GoralysConfig::SESSION::USER_CACHE as $key) {
                    unset($_SESSION[$key]);
                }
                $kernel->deferredResponse(401)->toast( // Unauthorized
                    ToastType::WARNING,
                    "Authentification",
                    "Vous devez vous connecter pour effectuer cette action",
                )
                    ->redirect("/user/login")
                    ->send();
            }

            return $next($kernel);
        }
        $kernel->requireAuth($this->endpoint);
        return $next($kernel);
    }
}
