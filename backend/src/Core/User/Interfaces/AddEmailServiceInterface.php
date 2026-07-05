<?php

namespace Goralys\Core\User\Interfaces;

interface AddEmailServiceInterface
{
    /**
     * Sets the email for a given user.
     * @param string $username The username of the user.
     * @param string $email The new email of the user.
     * @return bool Wether the operation was successful or not.
     */
    public function addEmail(string $username, string $email): bool;
}
