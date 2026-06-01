<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\Kernel\GoralysKernel;

/**
 * Middleware that validates the CSRF token for a given form before the route handler runs.
 */
final class CSRFMiddleware implements MiddlewareInterface
{
    private string $redirect;
    private string $formId;

    /**
     * @param string $route The matched route path.
     * @param mixed ...$params Expects [$formId, $redirect].
     */
    public function __construct(string $route, ...$params)
    {
        $this->formId = $params[0] ?? '';
        $this->redirect = $params[1] ?? "/";
    }

    /**
     * Returns the middleware binding for CSRF validation on a specific form.
     * @param string $formId The form identifier to validate the token against.
     * @param ?string $redirect The page to redirect to on failure (defaults to "/").
     * @return array The middleware descriptor array.
     */
    public static function form(string $formId, ?string $redirect = null): array
    {
        return ['csrf', [$formId, $redirect]];
    }

    /**
     * Validates the CSRF token for the configured form, aborting the request on failure.
     * @param GoralysKernel $kernel The application kernel.
     * @param callable $next The next handler in the pipeline.
     * @return mixed
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed
    {
        $kernel->requireCSRF($this->formId, $this->redirect);
        return $next($kernel);
    }
}
