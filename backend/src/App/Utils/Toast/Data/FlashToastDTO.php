<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Utils\Toast\Data;

/**
 * DTO used to transport a flash toast stored in the session.
 */
final readonly class FlashToastDTO
{
    /**
     * @param array<string, mixed> $toastInfo The toast notification payload data.
     * @param string $redirect The URL to redirect to after displaying the toast.
     * @param bool $flash Whether the toast is stored as a flash message in the session.
     * @param string $action The action identifier associated with the toast.
     */
    public function __construct(
        public array $toastInfo,
        public string $redirect,
        public bool $flash = false,
        public string $action = "",
    ) {
    }
}
