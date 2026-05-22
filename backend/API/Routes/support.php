<?php

use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\CSRFMiddleware;
use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Kernel\GoralysKernel;

function createSupportRoutes(GoralysRouter $router): void
{
    $router->post("support", function (GoralysKernel $kernel, RequestInterface $request) {
        $fullName = $_SESSION['current_full_name'] ?? "Anonyme";
        $reason = $request->param("reason");
        $message = "Il semblerait que l'utilisateur <b>" . $fullName . "</b> ait rencontré un problème :<br><br>"
            . "<strong>Mail de l'utilisateur:</strong> " . $request->param("user-email") . "<br><br>"
            . "<strong>Raison:</strong> " . $reason . "<br><br>"
            . "<strong>Message:</strong><br>"
            . nl2br($request->param("message"));

        $kernel->mailer->sendMail(
            "Problème - " . $fullName . "[" . $reason . "]",
            $message,
            "@admin",
        );

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Support",
            "Votre problème a bien été communiqué à notre équipe.",
        )
            ->redirect("/")
            ->send();
    }, RouterOptions::$INPUT::require("reason", "message", "user-email"))
        ->middleware(...RateLimitMiddleware::for("support-ticker", "/"))
        ->middleware(...CSRFMiddleware::form("support-ticket", "/"))
        ->middleware(...AuthMiddleware::weak())
        ->middleware(...ToastMiddleware::flash());
}
