<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Controllers;

use DateMalformedStringException;
use Goralys\App\User\Data\Enums\UserAuthStatus;
use Goralys\App\User\Data\RevokeTokenDTO;
use Goralys\App\User\Data\TokenCreateDTO;
use Goralys\App\User\Data\TokenLoginDTO;
use Goralys\Core\User\Data\AuthTokensCollection;
use Goralys\Core\User\Data\Enums\UserRole;
use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Repository\AuthRepository;
use Goralys\Core\User\Repository\Interfaces\AuthRepositoryInterface;
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
use Random\RandomException;

/**
 * The controller that handles the authentication logic (register, login, and logout).
 */
final class AuthController
{
    /**
     * @var UserRole[] This constant is used to block certain mobile users because their interface has no responsive
     * support yet.
     */
    public const array MOBILE_FORBIDDEN_ROLES = [UserRole::ADMIN];

    private LoggerInterface $logger;
    private DbContainerInterface $db;
    private UserRepositoryInterface $users;
    private AuthRepositoryInterface $repo;
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

        $this->users = new UserRepository($this->logger, $this->db);
        $this->repo = new AuthRepository($this->logger, $this->db);
    }

    /**
     * Registers a new user via a register service.
     * @param UserRegisterDTO $userData The necessary data to register the user.
     * @return bool If the creation was successful or not.
     */
    public function register(UserRegisterDTO $userData): bool
    {
        $userData->sanitize();

        $validator = new RegisterValidatorService($this->users);
        $roleGetter = new GetUserRoleService($this->users);
        $userCreator = new CreateUserService($this->users);
        $emailAdder = new AddEmailService($this->users);

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
     * Checks if a password is correct for the current user.
     * @param string $password The password to check.
     * @return bool Whether the password is correct.
     * @throws UserNotFoundException If the user does not exist.
     */
    public function validatePassword(string $password): bool
    {
        $service = new LoginService($this->logger, $this->users);
        return $service->checkPassword(new UserLoginDTO($_SESSION[Config::SESSION::USERNAME], $password));
    }

    /**
     * Log in the user via a login service.
     * @param UserLoginDTO $userData The necessary credentials to log in the user.
     * @return bool If the login was successful or not.
     * @throws DateMalformedStringException
     */
    public function login(UserLoginDTO $userData): bool
    {
        $service = new LoginService($this->logger, $this->users);

        try {
            if (!$service->login($userData)) {
                return false;
            }

            $this->cacheUserData($userData->username);
            return true;
        } catch (UserNotFoundException) {
            return false;
        }
    }

    /**
     * Helper to cache the user's data after a successful login.
     * This function also regenerates the session's id.
     * @param string $username The username which logged in successfully.
     * @throws UserNotFoundException|DateMalformedStringException If the user does not exists.
     */
    private function cacheUserData(string $username): void
    {
        session_regenerate_id(true);
        $sessionData = $this->users->getByUsername($username);

        $_SESSION[Config::SESSION::ID] = $sessionData->id;
        $_SESSION[Config::SESSION::FULL_NAME] = $sessionData->fullName;
        $_SESSION[Config::SESSION::USERNAME] = $sessionData->username;
        $_SESSION[Config::SESSION::PUBLIC_ID] = $this->users->getPublicIdForUsername($sessionData->username);
        $_SESSION[Config::SESSION::ROLE] = $sessionData->role->toString();
        $_SESSION[Config::SESSION::EMAIL] = $sessionData->email;

        $_SESSION['ua'] = hash("sha256", $_SERVER['HTTP_USER_AGENT']);
        $_SESSION['regen_time'] = time();

        $this->logger->debug(LoggerInitiator::APP, "New session: " . print_r($_SESSION, true));
    }

    /**
     * Creates a new authentication for a given user.
     * @param TokenCreateDTO $data The data necessary to create the authentication token.
     * @return ?string Null if the token could not be added to the user, the generated token elsewhise.
     * @throws RandomException If the token generation fails.
     */
    public function createToken(TokenCreateDTO $data): ?string
    {
        $token = bin2hex(random_bytes(Config::AUTH::AUTH_TOKEN_LENGTH));
        return $this->repo->addToken($data->username, hash(Config::AUTH::AUTH_TOKEN_ALGORITHM, $token), $data->name)
            ? $token
            : null;
    }

    /**
     * Tries to log a user in based on his username and a given auth token.
     * If the token is valid, the function automatically attempts to rotate it.
     * @param TokenLoginDTO $data The necessary credentials to log the user in with an authentication token.
     * @return ?string Null if the token is invalid or is the rotation fails, the new token elsewhise.
     * @throws RandomException|DateMalformedStringException If the new token generation fails.
     */
    public function tokenLogin(TokenLoginDTO $data): ?string
    {
        $new = bin2hex(random_bytes(Config::AUTH::AUTH_TOKEN_LENGTH));
        $oldHash = hash(Config::AUTH::AUTH_TOKEN_ALGORITHM, $data->token);
        $newHash = hash(Config::AUTH::AUTH_TOKEN_ALGORITHM, $new);
        $valid = $this->repo->isTokenValid($data->username, $oldHash);
        $rotate = $this->repo->rotateToken($data->username, $oldHash, $newHash);
        $result = $valid && $rotate;

        if (!$result) {
            $this->logger->debug(
                LoggerInitiator::APP,
                "Exit token login because of invalid result (" . (!$valid ? "invalid" : "not rotated") . ")"
            );
            return null;
        }

        try {
            $this->cacheUserData($data->username);
        } catch (UserNotFoundException $e) {
            $this->logger->debug(LoggerInitiator::APP, "Exit token login because of exception {$e->getMessage()}");
            return null;
        }
        $this->logger->debug(LoggerInitiator::APP, "Exit token login successfully");
        return $new;
    }

    /**
     * Revokes an authentication token.
     * @param RevokeTokenDTO $data The necessary data to identify and revoke the token.
     * @return bool Whether the token was revoked or not.
     */
    public function revokeToken(RevokeTokenDTO $data): bool
    {
        return $this->repo->revokeToken($data->username, $data->name);
    }

    /**
     * Gets all the auth tokens for a given user.
     * @param string $username The user to gets all the tokens for.
     * @return AuthTokensCollection All the auth tokens for the given user.
     * @throws DateMalformedStringException
     */
    public function getTokens(string $username): AuthTokensCollection
    {
        return $this->repo->getTokens($username);
    }

    /**
     * Logs the user out and destroys the session.
     * @return bool If the logout was successful or not.
     */
    public function logout(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $params = session_get_cookie_params();
        session_unset();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
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
     * The authentication cookie expires after an hour.
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
