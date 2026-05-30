<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Utils\Toast\Data;

/**
 * The DTO used to transport the data of a toast
 */
final class ToastDTO
{
    /**
     * @param array<string, mixed> $toastInfo The toast notification payload data.
     * @param string $redirect The URL to redirect to after displaying the toast.
     * @param bool   $flash Whether the toast should be stored as a flash notification.
     */
    public function __construct(
        public readonly array $toastInfo,
        public string $redirect,
        public readonly bool $flash = false,
    ) {
    }
}
