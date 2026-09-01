<?php

use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Router\Routes;
use Goralys\Kernel\GoralysKernel;

Routes::get("highschools/list", function (GoralysKernel $kernel) {
    $kernel->response()->json($kernel->highSchools->getAllSchools());
})
    ->middleware(...RateLimitMiddleware::for("get-high-schools"));

Routes::get("highschools/token", function (GoralysKernel $kernel, RequestInterface $request) {
    $token = $kernel->highSchools->getTokenForSchool($request->param("code"));

    if ($token === null) {
        $kernel->response(404)->http();
    }

    $kernel->response()->json(["token" =>  $token]);
}, RouterOptions::$INPUT::require("code"))
    ->middleware(...RateLimitMiddleware::for("get-high-school-token"));
