<?php

use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Kernel\GoralysKernel;

function createCoffeeTeaRoutes(GoralysRouter $router): void
{
    $router->brew('coffee', function (GoralysKernel $kernel) {
        $kernel->deferredResponse(418)->toast(
            ToastType::INFO,
            "I'm a teapot",
            "Je suis une théière, pas une machine à café !",
        )
            ->send();
    });

    $router->brew('tea', function (GoralysKernel $kernel) {
        $kernel->deferredResponse(418)->toast(
            ToastType::INFO,
            "Du thé ?",
            "Vous ne pensiez tout de même pas en avoir ?",
        )
            ->send();
    });

    $router->brew('cookies', function (GoralysKernel $kernel, RequestInterface $request) {
        match ($request->get('flavour')) {
            'chocolate' => $kernel->deferredResponse()->toast(
                ToastType::SUCCESS,
                "Cookies",
                "Voici votre cookie au chocolat !",
            )
                ->redirect('/')
                ->send(),
            'vanilla' => $kernel->deferredResponse()->toast(
                ToastType::SUCCESS,
                "Cookies",
                "Correct, mais sans originalité...",
            )
                ->redirect('/')
                ->send(),
            'raisin' => $kernel->deferredResponse(418)->toast(
                ToastType::ERROR,
                "Cookies",
                "Non. Absolument non.",
            )
                ->redirect('/')
                ->send(),
            default => $kernel->deferredResponse(400)->toast(
                ToastType::WARNING,
                "Cookies",
                "Vous avez de mauvais goûts en terme de cookies...",
            )
                ->redirect('/')
                ->send(),
        };
    }, ...RouterOptions::$INPUT::require('flavour'));
}
