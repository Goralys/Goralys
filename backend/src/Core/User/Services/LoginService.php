<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Services;

use Goralys\Core\User\Data\UserLoginDTO;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\User\UserNotFoundException;

/**
 * Service responsible for authenticating a user against stored credentials.
 */
final class LoginService
{
    private LoggerInterface $logger;
    private UserRepositoryInterface $repo;

    /**
     * Initializes the logger and the user repository used by the service.
     * @param LoggerInterface $logger The injected logger.
     * @param UserRepositoryInterface $repo The injected user repository.
     */
    public function __construct(
        LoggerInterface $logger,
        UserRepositoryInterface $repo,
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
    }

    /**
     * Logs in a user by using its password and username.
     * @param UserLoginDTO $userData The necessary credentials to log the user in.
     * @return bool If the login was successful or not.
     * @throws UserNotFoundException If the user is not found.
     */
    public function login(UserLoginDTO $userData): bool
    {
        if (!$this->checkPassword($userData)) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "Failed to connect user, invalid password " . " for user : " . $userData->username,
            );
            return false;
        }

        $this->logger->info(
            LoggerInitiator::CORE,
            "New user logged in : " . $userData->username,
        );
        return true;
    }

    /**
     * Checks if a given password is correct for a specific user.
     * @param UserLoginDTO $userData The necessary credentials to log the user in.
     * @return bool Whether the password is correct.
     * @throws UserNotFoundException If the user is invalid (does not exist).
     */
    public function checkPassword(UserLoginDTO $userData): bool
    {
        $login = $this->repo->getLoginDTO($userData->username);

        if ($login === null) {
            throw new UserNotFoundException("No such user : " . $userData->username);
        }

        $passwordHash = $login->password;

        if (!password_verify($userData->password, $passwordHash)) {
            return false;
        }
        return true;
    }
}
