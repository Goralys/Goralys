<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Utils\Toast\Services;

use Goralys\App\Utils\Toast\Data\ToastDTO;
use JsonSerializable;

/**
 * The service used to send a toast to the frontend
 */
final class ToastResponderService
{
    private ToastFlashService $flashService;

    public function __construct(ToastFlashService $flashService)
    {
        $this->flashService = $flashService;
    }

    /**
     * Sends a toast to the frontend.
     * If the {@see ToastDTO::$flash} property is set to `false`, the toast will be sent as a JSON object
     * and then parsed by the frontend.
     * @param ToastDTO $toastData The data of the toast.
     * @param string $action The action to perform when the toast is sent to the frontend.
     * @param array|JsonSerializable|null $json An optionnal field which allows to send extra data along with the toast.
     * @return void
     */
    public function sendToast(ToastDTO $toastData, string $action = "", array|JsonSerializable|null $json = null): void
    {
        if ($toastData->flash) {
            $this->flashService->store($toastData, $action);
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(
            array_merge($toastData->toastInfo, ["redirect" => $toastData->redirect], $json ?? []),
            JSON_UNESCAPED_UNICODE
        );
    }
}
