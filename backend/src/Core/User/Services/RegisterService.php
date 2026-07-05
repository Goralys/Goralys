<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Services;

use Goralys\Core\User\Data\UserCreateDTO;
use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Interfaces\AddEmailServiceInterface;
use Goralys\Core\User\Interfaces\CreateUserInterface;
use Goralys\Core\User\Interfaces\GetUserRoleInterface;
use Goralys\Core\User\Interfaces\RegisterValidatorServiceInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;

/**
 * Orchestrates the user registration flow by coordinating validation, role assignment, and persistence.
 */
final class RegisterService
{
    private LoggerInterface $logger;
    private RegisterValidatorServiceInterface $validator;
    private GetUserRoleInterface $roleGetter;
    private CreateUserInterface $userCreator;
    private AddEmailServiceInterface $emailAdder;

    /**
     * Initializes the logger and all the service's sub-services.
     * @param LoggerInterface $logger The injected logger
     * @param RegisterValidatorServiceInterface $validator The injected validator.
     * It is used to verify that the can register.
     * @param GetUserRoleInterface $roleGetter The injected role getter.
     * It is used to retrieve the user's role and assign iot automatically.
     * @param CreateUserInterface $userCreator The injected user creator.
     * It is used to create the user inside the database.
     */
    public function __construct(
        LoggerInterface $logger,
        RegisterValidatorServiceInterface $validator,
        GetUserRoleInterface $roleGetter,
        CreateUserInterface $userCreator,
        AddEmailServiceInterface $emailAdder,
    ) {
        $this->logger = $logger;

        $this->validator = $validator;
        $this->roleGetter = $roleGetter;
        $this->userCreator = $userCreator;
        $this->emailAdder = $emailAdder;
    }

    /**
     * Register a new user to the database.
     * @param UserRegisterDTO $data The necessary data to register the user.
     * @return bool If the register process was successful or not.
     */
    public function register(UserRegisterDTO $data): bool
    {
        if (!$this->validator->canRegister($data)) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "Failed to register user with user name : " . $data->username,
            );
            return false;
        }

        $createData = new UserCreateDTO(
            $data->username,
            password_hash($data->password, PASSWORD_DEFAULT),
            $this->roleGetter->getRoleByUsername($data->username),
        );

        if (!$this->userCreator->createUser($createData)) {
            $this->logger->error(
                LoggerInitiator::CORE,
                "Failed to create user with user name : " . $data->username,
            );
            return false;
        }

        // ensure fk from emails.username to users.usename is respected
        if ($data->email) {
            if (!$this->emailAdder->addEmail($data->username, $data->email)) {
                $this->logger->error(
                    LoggerInitiator::CORE,
                    "Failed to add email $data->email for user $data->username"
                );
            }
        }

        $this->logger->info(
            LoggerInitiator::CORE,
            "Successfully registered a new user with username : "
            . $data->username . "(" . $createData->role->toString() . ")",
        );
        return true;
    }
}
