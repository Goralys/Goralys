<?php

use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\CSRFMiddleware;
use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Middleware\RoleMiddleware;
use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\Support\Data\Enums\SupportReason;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Mail\Config\MailerConfig;
use Goralys\Shared\Config\GoralysConfig;

function createSupportRoutes(GoralysRouter $router): void
{
    $router->post("support/contact", function (GoralysKernel $kernel, RequestInterface $request) {
        $fullName = $_SESSION[GoralysConfig::SESSION::FULL_NAME] ?? "Anonyme";
        [$reason, $message] = [$request->param("reason"), $request->param("message")];
        $ticket = $kernel->support->createTicket(SupportReason::fromString($reason), $message);
        $message = "Il semblerait que l'utilisateur <b>" . htmlspecialchars($fullName) . "</b> ait rencontré un problème :<br><br>"
                . "<strong>Mail de l'utilisateur:</strong> " . htmlspecialchars($request->param("user-email")) . "<br><br>"
                . "<strong>Raison:</strong> " . htmlspecialchars($reason) . "<br><br>"
                . "<strong>Message:</strong><br>"
                . nl2br(htmlspecialchars($message)) . "<br><br>"
                . "<strong>Consulter le ticket sur Goralys: " . htmlspecialchars($kernel->env->getByKey("ORIGIN_DOMAIN"))
                . "support/ticket?t=" . $ticket->id . "</strong>";

        $kernel->mailer->sendMail(
            MailerConfig::SUPPORT_ALIAS,
            "Problème - " . $fullName . "[" . $reason . "]",
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
    }, RouterOptions::$INPUT::require("reason", "message", "user-email"))
            ->middleware(...RateLimitMiddleware::for("support-ticket", "/"))
            ->middleware(...CSRFMiddleware::form("support-ticket", "/"))
            ->middleware(...AuthMiddleware::weak())
            ->middleware(...ToastMiddleware::flash());

    $router->get("support/tickets", function (GoralysKernel $kernel) {
        $kernel->response()->json($kernel->support->getTickets()); // OK
    })
            ->middleware(...RoleMiddleware::require(UserRole::ADMIN, true))
            ->middleware(...RateLimitMiddleware::for("get-tickets", "/"))
            ->middleware(...CSRFMiddleware::form("get-tickets", "/"))
            ->middleware(...AuthMiddleware::require());

    $router->get("support/ticket", function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$ticket = $kernel->support->getTicket($request->param("t"))) {
            $kernel->deferredResponse(400)->toast( // Bad Request
                ToastType::ERROR,
                "Ticket de support",
                "Aucun ticket de support ne correspond à l'id donné."
            )
                ->redirect("/support/tickets")
                ->send();
        }
        $kernel->response()->json($ticket); // OK
    }, ...RouterOptions::$INPUT::require("t"))
            ->middleware(...RoleMiddleware::require(UserRole::ADMIN, true))
            ->middleware(...RateLimitMiddleware::for("get-ticket", "/"))
            ->middleware(...CSRFMiddleware::form("get-ticket", "/"))
            ->middleware(...AuthMiddleware::require())
            ->middleware(...ToastMiddleware::flash());
}
