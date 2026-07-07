<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Controllers;

use Goralys\App\User\Data\Enums\UserAuthStatus;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Core\User\Repository\UserRepository;
use Goralys\Core\User\Services\AddEmailService;
use Goralys\Core\User\Services\CreateUserService;
use Goralys\Core\User\Services\GetUserRoleService;
use Goralys\Core\User\Services\LoginService;
use Goralys\Core\User\Services\RegisterService;
use Goralys\Core\User\Services\RegisterValidatorService;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\User\UserNotFoundException;

/**
 * The controller that handles the authentification logic (register, login, and logout).
 */
final class AuthController
{
    /**
     * @var UserRole[] This constant is used to block certain mobile users because their interface has no responsive
     * support yet.
     */
    public const array MOBILE_FORBIDDEN_ROLES = [UserRole::TEACHER, UserRole::ADMIN];

    private LoggerInterface $logger;
    private DbContainerInterface $db;
    private UserRepositoryInterface $repo;
    /**
     * The lifetime of the PHP session, the kernel passes this variable when the controller is constructed.
     * @var int
     */
    private readonly int $sessionLifetime;
    private readonly float $sessionMultiplier;

    /**
     * Initializes the logger and the database container used by the controller.
     * @param LoggerInterface $logger The injected logger.
     * @param DbContainerInterface $db The injected database container.
     * @param int $sessionLifetime The lifetime of the PHP session.
     * @param float $sessionLifetimeMultiplier The lifetime multiplier of the PHP session.
     */
    public function __construct(
        LoggerInterface $logger,
        DbContainerInterface $db,
        int $sessionLifetime,
        float $sessionLifetimeMultiplier,
    ) {
        $this->logger = $logger;
        $this->db = $db;
        $this->sessionLifetime = $sessionLifetime;
        $this->sessionMultiplier = $sessionLifetimeMultiplier;

        $this->repo = new UserRepository($this->logger, $this->db);
    }

    /**
     * Registers a new user via a register service.
     * @param UserRegisterDTO $userData The necessary data to register the user.
     * @return bool If the creation was successful or not.
     */
    public function register(UserRegisterDTO $userData): bool
    {
        $userData->sanitize();

        $validator = new RegisterValidatorService($this->repo);
        $roleGetter = new GetUserRoleService($this->repo);
        $userCreator = new CreateUserService($this->repo);
        $emailAdder = new AddEmailService($this->repo);

        $service = new RegisterService(
            $this->logger,
            $validator,
            $roleGetter,
            $userCreator,
            $emailAdder
        );
        return $service->register($userData);
    }

    /**
     * Log in the user via a login service.
     * @param UserLoginDTO $userData The necessary credentials to log in the user.
     * @return bool If the login was successful or not.
     */
    public function login(UserLoginDTO $userData): bool
    {
        $service = new LoginService($this->logger, $this->repo);

        try {
            if (!$service->login($userData)) {
                return false;
            }

            session_regenerate_id(true);
            $sessionData = $this->repo->getByUsername($userData->username);

            $_SESSION[Config::SESSION::ID] = $sessionData->id;
            $_SESSION[Config::SESSION::FULL_NAME] = $sessionData->fullName;
            $_SESSION[Config::SESSION::USERNAME] = $sessionData->username;
            $_SESSION[Config::SESSION::PUBLIC_ID] = $this->repo->getPublicIdForUsername($sessionData->username);
            $_SESSION[Config::SESSION::ROLE] = $sessionData->role->toString();
            $_SESSION[Config::SESSION::EMAIL] = $sessionData->email;

            $_SESSION['ua'] = hash("sha256", $_SERVER['HTTP_USER_AGENT']);
            $_SESSION['regen_time'] = time();
            $this->logger->debug(LoggerInitiator::APP, "New session: " . print_r($_SESSION, true));
            return true;
        } catch (UserNotFoundException) {
            return false;
        }
    }

    /**
     * Logs the user out and destroys the session.
     * @return bool If the logout was successful or not.
     */
    public function logout(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false; // already logged out, do nothing
        }

        session_unset();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        return true;
    }

    /**
     * Logs the user out but preserves the session.
     * @return bool If the logout was successful or not.
     */
    public function softLogout(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false; // already logged out, do nothing
        }

        unset(
            $_SESSION[Config::SESSION::ID],
            $_SESSION[Config::SESSION::FULL_NAME],
            $_SESSION[Config::SESSION::USERNAME],
            $_SESSION[Config::SESSION::PUBLIC_ID],
            $_SESSION[Config::SESSION::ROLE],
            $_SESSION[Config::SESSION::EMAIL],
        );

        session_regenerate_id(true);

        return true;
    }

    /**
     * Checks if the user is authenticated.
     * The authentification cookie expires after an hour.
     * @param int $sinceLastConnection The time elapsed since the last user connection
     * @return UserAuthStatus If the user is authenticated.
     */
    public function getAuthStatus(int $sinceLastConnection): UserAuthStatus
    {
        if (!isset($_SESSION) || !isset($_SESSION[Config::SESSION::ID])) {
            return UserAuthStatus::NOT_AUTHENTICATED;
        } elseif (
            $sinceLastConnection > $this->sessionMultiplier * $this->sessionLifetime
            || $sinceLastConnection === -1
        ) {
            return UserAuthStatus::NOT_AUTHENTICATED;
        }

        return $sinceLastConnection > $this->sessionLifetime
                ? UserAuthStatus::SESSION_EXPIRED
                : UserAuthStatus::AUTHENTICATED;
    }
}
