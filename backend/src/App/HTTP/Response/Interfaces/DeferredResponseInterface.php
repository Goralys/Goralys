<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Response\Interfaces;

use Goralys\App\Context\AppContext;
use Goralys\App\HTTP\JSON\Interfaces\JsonResponder;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use JetBrains\PhpStorm\NoReturn;

/**
 * A contract used to represent a deferred response.
 */
interface DeferredResponseInterface
{
    /**
     * @param AppContext $context The current context of the application.
     * @param JsonResponder $json The JSON responder service.
     * @param int $responseCode The HTTP code of the response.
     */
    public function __construct(AppContext $context, JsonResponder $json, int $responseCode = 200);

    /**
     * Attaches a toast to the response.
     * @param ToastType $type The type of the toast.
     * @param string $title The toast title.
     * @param string $message The toast message.
     * @return self
     */
    public function toast(ToastType $type, string $title, string $message): self;

    /**
     * Attaches a pre-configured error toast to the response.
     * @param string $message The error message.
     * @return self
     */
    public function error(string $message): self;

    /**
     * Sets the redirect path for the toast.
     * @param string $path The path to redirect the user to.
     * @return self
     */
    public function redirect(string $path): self;

    /**
     * Sets the frontend action triggered by the toast.
     * @param string $action The action to trigger.
     * @return self
     */
    public function action(string $action): self;

    /**
     * Sends the response and terminates.
     * @return never
     */
    #[NoReturn]
    public function send(): never;
}
