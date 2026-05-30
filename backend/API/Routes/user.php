<?php

use Goralys\App\HTTP\Middleware\AuthMiddleware;
use Goralys\App\HTTP\Middleware\CSRFMiddleware;
use Goralys\App\HTTP\Middleware\DbMiddleware;
use Goralys\App\HTTP\Middleware\MiddlewareSets;
use Goralys\App\HTTP\Middleware\RateLimitMiddleware;
use Goralys\App\HTTP\Middleware\ToastMiddleware;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\Router\GoralysRouter;
use Goralys\App\Router\Options\RouterOptions;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Utils\String\Data\StringCase;

function createUserRoutes(GoralysRouter $router): void
{
    // ================================================
    // [SECTION] Profile
    // ================================================
    $router->get('user/profile', function (GoralysKernel $kernel) {
        $data = [
            "username"   => trim($_SESSION[GoralysConfig::SESSION::USERNAME]),
            "full_name"  => trim($_SESSION[GoralysConfig::SESSION::FULL_NAME]),
            "role"       => trim($_SESSION[GoralysConfig::SESSION::ROLE]),
            "public_id"  => trim($_SESSION[GoralysConfig::SESSION::PUBLIC_ID]),
            ...(isset($_SESSION[GoralysConfig::SESSION::EMAIL])
                    ? ["email" => trim($_SESSION[GoralysConfig::SESSION::EMAIL])]
                    : []
            ),
        ];

        $kernel->logger->info(
            LoggerInitiator::APP,
            "Accessed data of user: " . $data["username"],
        );

        $kernel->response()->json(
            [
                "success" => true,
                "data" => $data,
            ],
        );
    })
        ->middleware(...RateLimitMiddleware::for("get-profile"))
        ->middleware(...AuthMiddleware::weak());

    $router->get('user/role', function (GoralysKernel $kernel) {
        $kernel->logger->info(
            LoggerInitiator::APP,
            "Accessed data of user: " . $_SESSION[GoralysConfig::SESSION::USERNAME],
        );


        $kernel->response()->json(
            [
                "success" => true,
                "role" => trim($_SESSION[GoralysConfig::SESSION::ROLE]),
            ],
        );
    })
        ->middleware(...RateLimitMiddleware::for("get-role"))
        ->middleware(...AuthMiddleware::weak());

    $router->put("user/email", function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->setEmail($request->param("email"))) {
            $kernel->deferredResponse(500)->toast( // Bad Request
                ToastType::ERROR,
                "Adresse mail",
                "Nous n'avons pas pu mettre à jour votre adresse mail, veuillez réessayer ultérieurement.",
            )
                ->redirect("/user/profile")
                ->send();
        }

        $kernel->deferredResponse()->toast( // OK
            ToastType::SUCCESS,
            "Adresse mail",
            "Votre adresse mail a bien été mise à jour.",
        )
            ->redirect("/user/profile")
            ->send();
    }, ...RouterOptions::$INPUT::require("email"))
        ->middleware(...RateLimitMiddleware::for("email-update"))
        ->middleware(...CSRFMiddleware::form("update-email"))
        ->middleware(...AuthMiddleware::weak())
        ->middleware(...DbMiddleware::require());

    $router->delete("user/email", function (GoralysKernel $kernel) {
        if (!$kernel->users->removeEmail()) {
            $kernel->deferredResponse(500)->toast( // Bad Request
                ToastType::ERROR,
                "Adresse mail",
                "Nous n'avons pas pu supprimer votre adresse mail, veuillez réessayer ultérieurement.",
            )
                ->redirect("/user/profile")
                ->send();
        }

        $kernel->deferredResponse()->toast( // OK
            ToastType::SUCCESS,
            "Adresse mail",
            "Votre adresse mail a bien été supprimée.",
        )
            ->redirect("/user/profile")
            ->send();
    })
        ->middleware(...RateLimitMiddleware::for("email-update"))
        ->middleware(...CSRFMiddleware::form("delete-email"))
        ->middleware(...AuthMiddleware::weak())
        ->middleware(...DbMiddleware::require());

    // ================================================
    // [SECTION] Auth
    // ================================================
    $router->post('user/register', function (GoralysKernel $kernel, RequestInterface $request) {
        $registerData = new UserRegisterDTO(
            $request->param("user-name"),
            $request->param("first-name") . " " . $request->param("last-name"),
            $request->param("password"),
        );

        if (!$kernel->auth->register($registerData)) {
            $kernel->deferredResponse(500)->error( // Internal Server Error
                "Une erreur interne est survenue lors de la création du compte, veuillez réessayer ultérieurement.",
            )
                ->redirect("/user/register")
                ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::SUCCESS,
            "Création du compte",
            "Votre compte chez Goralys a bien été créé. Vous pouvez maintenant vous connecter.",
        )
            ->redirect("/user/login")
            ->send();
    }, ...RouterOptions::$INPUT::require("user-name", "password", "first-name", "last-name"))
        ->middleware(...CSRFMiddleware::form('register', '/user/register'))
        ->middleware(...DbMiddleware::require())
        ->middleware(...ToastMiddleware::flash());

    $router->post('user/login', function (GoralysKernel $kernel, RequestInterface $request) {
        $userData = new UserLoginDTO(
            $request->param("username"),
            $request->param("password"),
        );

        if (!$kernel->auth->login($userData)) {
            $kernel->deferredResponse(401)->toast(
                ToastType::ERROR,
                "Connexion",
                "Mot de passe ou identifiant incorrect.",
            )
                ->redirect("/user/login")
                ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::SUCCESS,
            "Connexion",
            "Vous avez bien été connecté à votre compte.",
        )
            ->redirect("/subject")
            ->action("login-success")
            ->send();
    }, ...RouterOptions::$INPUT::require("username", "password"))
        ->middleware(...RateLimitMiddleware::for(
            'login',
            '/user/login',
            "Tentatives de connexion trop nombreuses, veuillez réessayer dans quelques minutes",
        ))
        ->middleware(...CSRFMiddleware::form('login', '/user/login'))
        ->middleware(...DbMiddleware::require())
        ->middleware(...ToastMiddleware::flash());

    $router->post('user/logout', function (GoralysKernel $kernel) {
        $kernel->auth->logout();
        $kernel->response()->http();
    })
        ->middleware(...RateLimitMiddleware::for('logout'))
        ->middleware(...CSRFMiddleware::form('logout'))
        ->middleware(...DbMiddleware::require());

    // ================================================
    // [SECTION] Admin actions
    // ================================================
    $router->get('users/all', function (GoralysKernel $kernel) {
        $kernel->response()->json($kernel->users->getAll());
    })
        ->middlewares(...MiddlewareSets::adminPanelRoute('get-all-users', fetch: true))
        ->middleware(...DbMiddleware::require());

    $router->get('users/virtual', function (GoralysKernel $kernel) {
        $kernel->response()->json($kernel->users->getVirtual());
    })
            ->middlewares(...MiddlewareSets::adminPanelRoute('get-virtual-users', fetch: true))
            ->middleware(...DbMiddleware::require());

    $router->get('admins/all', function (GoralysKernel $kernel) {
        $kernel->response()->json($kernel->users->getAdmins());
    })
            ->middlewares(...MiddlewareSets::adminPanelRoute('get-all-admins', fetch: true))
            ->middleware(...DbMiddleware::require());

    $router->get('admins/virtual', function (GoralysKernel $kernel) {
        $kernel->response()->json($kernel->users->getAdminsVirtual());
    })
            ->middlewares(...MiddlewareSets::adminPanelRoute('get-virtual-admins', fetch: true))
            ->middleware(...DbMiddleware::require());

    // -------------------------
    // [SUB SECTION] Admins create and revoke
    // -------------------------

    $router->post('admin/create', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(501)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/admin")
                    ->send();
        }

        $result = $kernel->users->addAdmin(
            trim($kernel->utils->string->sanitize($request->param("last-name"), StringCase::UPPER))
            . " " . trim($request->param("first-name")),
        );

        if (!$result) {
            $kernel->deferredResponse(500)->error(
                "L'administrateur n'a pas pu être créé.",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Remplacement",
            "L'administrateur a bien été créé. Il peut désormais créer un compte avec l'identifiant $result.",
        )
                ->redirect("/admin/user")
                ->send();
    }, ...RouterOptions::$INPUT::require("first-name", "last-name", "admin-password"))
            ->middlewares(...MiddlewareSets::adminPanelRoute('create-admin', '/admin/admin'))
            ->middleware(...DbMiddleware::transaction());

    $router->delete('admin/revoke', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(401)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/admin")
                    ->send();
        }

        if ($request->param("target") === $_SESSION[GoralysConfig::SESSION::PUBLIC_ID]) {
            $kernel->deferredResponse(400)->toast( // Bad Request
                ToastType::WARNING,
                "Suppression",
                "Vous ne pouvez pas vous révoquez vous-même",
            )
                    ->redirect("/admin/admin")
                    ->send();
        }

        $kernel->users->revokeAdmin($request->param("target"));

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Suppression",
            "L'administrateur a bien été révoqué.",
        )
                ->redirect("/admin/user")
                ->send();
    }, ...RouterOptions::$INPUT::require("target", "admin-password"))
            ->middlewares(...MiddlewareSets::adminPanelRoute('revoke-admin', '/admin/admin'))
            ->middleware(...DbMiddleware::transaction());

    $router->patch('users/reset-password', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(501)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        if (!$kernel->users->resetPassword($request->param("target"))) {
            $kernel->deferredResponse(500)->error(
                "Le mot de passe n'a pas pu être réinitialisé.",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Mot de passe",
            "Le mot de passe a bien été réinitialisé, l'utilisateur peut maintenant recréer son compte.",
        )
                ->redirect("/admin/user")
                ->send();
    }, ...RouterOptions::$INPUT::require("target", "admin-password"))
            ->middlewares(...MiddlewareSets::adminPanelRoute('reset-password'))
            ->middleware(...DbMiddleware::require());

    $router->delete('users', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(501)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        if (!$kernel->users->delete($request->param("target"))) {
            $kernel->deferredResponse(500)->error(
                "L'utilisateur n'a pas pu être supprimé.",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Suppression du compte",
            "L'utilisateur a bien été supprimé",
        )
                ->redirect("/admin/user")
                ->send();
    }, ...RouterOptions::$INPUT::require("target", "admin-password"))
            ->middlewares(...MiddlewareSets::adminPanelRoute('delete-user'))
            ->middleware(...DbMiddleware::transaction());

    // -------------------------
    // [SUB SECTION] User replacement
    // -------------------------

    $router->put('users/teacher/replace', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(501)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $result = $kernel->users->replaceTeacher(
            $request->param("target"),
            trim($kernel->utils->string->sanitize($request->param("last-name"), StringCase::UPPER))
            . " " . trim($request->param("first-name")),
        );

        if (!$result) {
            $kernel->deferredResponse(500)->error(
                "Le professeur n'a pas pu être remplacé.",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Remplacement",
            "Le professeur a bien été remplacé. Il peut désormais créer un compte avec l'identifiant $result.",
        )
                ->redirect("/admin/user")
                ->send();
    }, ...RouterOptions::$INPUT::require("target", "first-name", "last-name", "admin-password"))
            ->middlewares(...MiddlewareSets::adminPanelRoute('replace-teacher'))
            ->middleware(...DbMiddleware::transaction());

    $router->get('users/username', function (GoralysKernel $kernel, RequestInterface $request) {
        if (!$kernel->users->validatePassword($request->param("admin-password"))) {
            $kernel->deferredResponse(501)->toast( // Unauthorized
                ToastType::WARNING,
                "Mot de passe",
                "Veuillez saisir le bon mot de passe",
            )
                    ->redirect("/admin/user")
                    ->send();
        }

        $kernel->deferredResponse()->toast(
            ToastType::INFO,
            "Identifiant",
            "Identifiant pour ce compte: " . $kernel->usernameManager->get($request->param("target")),
        )
                ->redirect("/admin/user")
                ->send();
    })
            ->middlewares(...MiddlewareSets::adminPanelRoute('get-username'))
            ->middleware(...DbMiddleware::require());
}
