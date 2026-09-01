<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Utils\Toast\Services;

use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\App\Utils\Toast\Data\ToastDTO;

/**
 * The service used to build a toast DTO from its info.
 */
final class ToastBuilderService
{
    /**
     * Builds a toast DTO from its type, title, message, redirect, and isJS property.
     * @param ToastType $toastType The type of the toast
     * @param string $toastTitle The title of the toast
     * @param string $toastMessage The message of the toast
     * @param ?string $redirect The page to redirect the user to, or `null` if no redirect is needed.
     * @param bool $flash If the toast is flash or not.
     * @return ToastDTO The data of the toast
     */
    public function buildToast(
        ToastType $toastType,
        string $toastTitle,
        string $toastMessage,
        ?string $redirect,
        bool $flash = false,
    ): ToastDTO {
        $toastInfo = [
            "toast" => true,
            "toastType" => $toastType->value,
            "toastTitle" => $toastTitle,
            "toastMessage" => $toastMessage,
            ...($redirect ? ["redirect" =>  $redirect] : []),
        ];
        return new ToastDTO(
            $toastInfo,
            $redirect,
            $flash,
        );
    }
}
