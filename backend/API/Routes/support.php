<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\DbMiddleware;
use Goralys\App\HTTP\Middleware\MiddlewareSets;
use Goralys\App\HTTP\Middleware\RoleMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Router\Routes;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Mail\Config\MailerConfig;
use Goralys\Shared\Config\GoralysConfig;

Routes::post("support/contact", function (GoralysKernel $kernel, RequestInterface $request) {
    $fullName = $_SESSION[GoralysConfig::SESSION::FULL_NAME] ?? $request->param("full-name") ?? "Anonyme";
    [$reason, $message] = [SupportReason::fromString($request->param("reason")), $request->param("message")];
    $id = $kernel->support->createTicket($reason, $request->param("user-email"), $message);

    if (!$id) {
        $kernel->deferredResponse()->toast(
            ToastType::ERROR,
            "Support",
            "Votre problème n'a pas pu être communiqué à notre équipe, veuillez réssayer ultérieurement.",
        )
                ->redirect("/")
                ->send();
    }

    $message =
            "Il semblerait que l'utilisateur <b>" . htmlspecialchars($fullName) . "</b> ait rencontré un problème :
            <br><br>"
            . "<strong>Mail de l'utilisateur:</strong> " . htmlspecialchars($request->param("user-email"))
            . "<br><br>"
            . "<strong>Raison:</strong> " . htmlspecialchars(SupportReason::getDisplay($reason)) . "<br><br>"
            . "<strong>Message:</strong><br>"
            . nl2br(htmlspecialchars($message)) . "<br><br>"
            . "<strong>Consulter le ticket sur Goralys: "
            . htmlspecialchars($kernel->getOriginDomain()) . "support/ticket?t=" . $id . "</strong>";

    $kernel->mailer->sendMail(
        MailerConfig::SUPPORT_ALIAS,
        "Problème - " . $fullName . "[" . SupportReason::getDisplay($reason) . "]",
        $message,
        "@admin", // broadcast to all admins
    );

    $kernel->deferredResponse()->toast(
        ToastType::INFO,
        "Support",
        "Votre problème a bien été communiqué à notre équipe.",
    )
            ->redirect("/")
            ->send();
}, ...RouterOptions::$INPUT::require("reason", "message", "user-email"),
   ...RouterOptions::$TOAST::flash())
        ->middlewares(...MiddlewareSets::supportRoute("support-ticket", [UserRole::STUDENT, false]));

Routes::get("support/tickets", function (GoralysKernel $kernel) {
    $kernel->response()->json($kernel->support->getTickets()); // OK
})
        ->middlewares(...MiddlewareSets::supportRoute("get-tickets"));

Routes::get("support/ticket", function (GoralysKernel $kernel, RequestInterface $request) {
    if (!$ticket = $kernel->support->getTicket($request->param("t"))) {
        $kernel->deferredResponse(400)->toast( // Bad Request
            ToastType::ERROR,
            "Ticket de support",
            "Aucun ticket de support ne correspond à l'identifiant donné."
        )
                ->redirect("/support")
                ->send();
    }
    $kernel->response()->json($ticket); // OK
}, ...RouterOptions::$INPUT::require("t"),
   ...RouterOptions::$TOAST::flash())
        ->middlewares(...MiddlewareSets::supportRoute("get-ticket"));

Routes::get("ticket/contact", function (GoralysKernel $kernel, RequestInterface $request) {
    if (!$ticket = $kernel->support->getTicket($request->param("t"))) {
        $kernel->deferredResponse(400)->toast( // Bad Request
            ToastType::ERROR,
            "Ticket de support",
            "Aucun ticket de support ne correspond à l'identifiant donné."
        )
                ->redirect("/support")
                ->send();
    }

    $kernel->response(302)->redirect( // Redirect
        "mailto:" . urlencode($ticket->email) . "?subject=" . urlencode(
            "RE #" . $ticket->id . " [" . SupportReason::getDisplay($ticket->reason) . "]"
        )
    );
}, ...RouterOptions::$INPUT::require("t"))
        ->middleware(...AuthMiddleware::require())
        ->middleware(...RoleMiddleware::require(UserRole::ADMIN, true))
        ->middleware(...DbMiddleware::require());

Routes::patch("ticket/resolve", function (GoralysKernel $kernel, RequestInterface $request) {
    if (!$kernel->support->resolveTicket($request->param("t"), $request->param("message"), $kernel->mailer)) {
        $kernel->deferredResponse(500)->toast( // Internal server error
            ToastType::ERROR,
            "Ticket de support",
            "Le ticket de support n'a pas pu être résolu (supprimé). Veuillez réessayer ultérieurement."
        )
                ->redirect("/support")
                ->send();
    }

    $kernel->deferredResponse()->toast( // OK
        ToastType::SUCCESS,
        "Ticket de support",
        "Le ticket a bien été résolu (supprimé). Un email automatique a été envoyé à l'utilisateur."
    )
            ->redirect("/support")
            ->send();
}, ...RouterOptions::$INPUT::require("t", "message"))
        ->middlewares(...MiddlewareSets::supportRoute("resolve-ticket"));
