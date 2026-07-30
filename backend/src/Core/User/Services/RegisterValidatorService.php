<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\User\Services;

use Goralys\Core\User\Data\UserRegisterDTO;
use Goralys\Core\User\Interfaces\RegisterValidatorServiceInterface;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;

/**
 * Service used to determine if a user can register or not.
 */
final class RegisterValidatorService implements RegisterValidatorServiceInterface
{
    private UserRepositoryInterface $repo;

    /**
     * Initializes the user repository used by the service.
     * @param UserRepositoryInterface $repo The injected user repository.
     */
    public function __construct(
        UserRepositoryInterface $repo,
    ) {
        $this->repo = $repo;
    }


    /**
     * Checks if a user can register.
     * @param UserRegisterDTO $data The user's data.
     * @return bool If the user can register or not.
     */
    public function canRegister(UserRegisterDTO $data): bool
    {
        $exists = $this->repo->exists($data->username);
        $valid = $this->repo->isUsernameValid($data->username);

        return $valid && !$exists;
    }
}
