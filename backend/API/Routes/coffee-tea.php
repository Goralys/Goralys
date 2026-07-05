<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Router\Routes;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Kernel\GoralysKernel;

Routes::brew('coffee', function (GoralysKernel $kernel) {
        $kernel->deferredResponse(418)->toast(
            ToastType::INFO,
            "I'm a teapot",
            "Je suis une théière, pas une machine à café !",
        )
            ->send();
});

Routes::brew('tea', function (GoralysKernel $kernel) {
    $kernel->deferredResponse(418)->toast(
        ToastType::INFO,
        "Du thé ?",
        "Vous ne pensiez tout de même pas en avoir ?",
    )
        ->send();
});

Routes::brew('cookies', function (GoralysKernel $kernel, RequestInterface $request) {
    match ($request->param('flavour')) {
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
