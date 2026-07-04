<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Router\Routes;
use Goralys\Kernel\GoralysKernel;

// ================================================
// [SECTION] CSRF
// ================================================
Routes::post('csrf', function (GoralysKernel $kernel, RequestInterface $request) {
    $formId = $request->param("form");

    if (!$kernel->csrf->create($formId)) {
        $kernel->response(500)->http(); // Internal Server Error
    }


    $kernel->response()->json([
        "csrf-token" => $kernel->csrf->getForForm($formId),
    ]);
}, ...RouterOptions::$INPUT::require('form'))
        ->middleware(...RateLimitMiddleware::for('csrf-create'));
