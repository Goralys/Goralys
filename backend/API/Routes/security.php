<?php

use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\Kernel\GoralysKernel;

function createSecurityRoutes(GoralysRouter $router): void
{
    // ================================================
    // [SECTION] CSRF
    // ================================================
    $router->post('csrf/create', function (GoralysKernel $kernel, RequestInterface $request) {
        $formId = $request->param("form-id");

        if (!$kernel->csrf->create($formId)) {
            $kernel->response(500)->http(); // Internal Server Error
        }


        $kernel->response()->json([
            "csrf-token" => $kernel->csrf->getForForm($formId),
        ]);
    }, ...RouterOptions::$INPUT::require('form-id'))
            ->middleware(...RateLimitMiddleware::for('csrf-create'));
}
