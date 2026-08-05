<?php

namespace Goralys\Core\User\Services;

use Goralys\Core\User\Interfaces\AddEmailServiceInterface;
use Goralys\Core\User\Repository\Interfaces\UserRepositoryInterface;

/**
 * This service is used to add an email to a user on registration.
 */
final class AddEmailService implements AddEmailServiceInterface
{
    private UserRepositoryInterface $repo;

    /**
     * @param UserRepositoryInterface $repo The injected user repository.
     */
    public function __construct(
        UserRepositoryInterface $repo,
    ) {
        $this->repo = $repo;
    }

    /**
     * Sets the email for a given user.
     * @param string $username The username of the user.
     * @param string $email The new email of the user.
     * @return bool Whether the operation was successful or not.
     */
    public function addEmail(string $username, string $email): bool
    {
        return $this->repo->setEmail($username, $email);
    }
}
