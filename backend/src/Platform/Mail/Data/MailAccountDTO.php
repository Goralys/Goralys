<?php

namespace Goralys\Platform\Mail\Data;

/*
     * The DTO used to represent mail account information (address and display name)
 */
class MailAccountDTO
{
    /**
     * @param string $address The address of the account.
     * @param string $displayName The display name of the account.
     */
    public function __construct(
        public string $address,
        public string $displayName,
    ) {
    }
}
