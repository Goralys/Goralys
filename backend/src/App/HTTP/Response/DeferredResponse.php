<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Response;

use Goralys\App\Context\AppContext;
use Goralys\App\Context\Data\Client;
use Goralys\App\Context\Data\ToastMode;
use Goralys\App\HTTP\JSON\Interfaces\JsonResponder;
use Goralys\App\Utils\Toast\Controllers\ToastController;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\App\Utils\Toast\Data\ToastDTO;
use Goralys\Shared\Exception\GoralysRuntimeException;
use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;

/**
 * A deferred HTTP response that buffers a toast notification before sending.
 *
 * Unlike {@see ImmediateResponse}, this response is built incrementally via a
 * fluent interface and only sent when {@see send()} is explicitly called.
 *
 * Supports two delivery modes driven by {@see AppContext::$mode}:
 * - {@see ToastMode::DEFAULT}: sends the toast payload with the configured HTTP code.
 * - {@see ToastMode::FLASH}: issues a 302 redirect and stores the toast in the session.
 */
final class DeferredResponse implements Interfaces\DeferredResponseInterface
{
    private AppContext $context;
    private JsonResponder $json;
    private ?ToastDTO $toast = null;
    private ?string $action = null;
    private array|JsonSerializable|null $data = null;
    private ToastController $controller;
    private int $code;

    /**
     * @param AppContext $context The current application context.
     * @param JsonResponder $json The JSON responder service.
     * @param int $responseCode The HTTP code of the response.
     */
    public function __construct(AppContext $context, JsonResponder $json, int $responseCode = 200)
    {
        $this->context = $context;
        $this->json = $json;
        $this->code = $responseCode;
        $this->controller = new ToastController();
    }

    /**
     * Attaches a toast notification to the response.
     * @param ToastType $type The type of the toast (e.g. success, warning, error).
     * @param string $title The toast title.
     * @param string $message The toast message.
     * @return self
     */
    public function toast(ToastType $type, string $title, string $message): self
    {
        $this->toast = $this->controller->builder->buildToast(
            $type,
            $title,
            $message,
            "/",
            $this->context->mode === ToastMode::FLASH,
        );
        return $this;
    }

    /**
     * Attaches a pre-configured error toast to the response.     *
     * @param string $message The error message to display.
     * @return self
     */
    public function error(string $message): self
    {
        $this->toast = $this->controller->builder->buildToast(
            ToastType::ERROR,
            "Erreur",
            $message,
            "/",
            $this->context->mode === ToastMode::FLASH,
        );
        return $this;
    }

    /**
     * Sets the redirect destination for the toast.
     * The path is appended to the origin domain from the current {@see AppContext}
     * and normalized with a trailing slash.
     * Must be called after {@see toast()} or {@see error()}.
     * @param string $path The relative path to redirect to (e.g. `/dashboard`).
     * @return self
     */
    public function redirect(string $path): self
    {
        $destination = trim($this->context->originDomain, "/") . "/" . trim($path, "/");
        if ($destination !== "/") {
            $destination = $destination . "/";
        }
        $this->toast->redirect = $destination;
        return $this;
    }

    /**
     * Attaches a frontend action identifier to the response.
     * The action string is forwarded to the frontend alongside the toast, where it can trigger a registered handler.
     * Refer to the frontend root layout for supported action identifiers.
     * @param string $action The action identifier.
     * @return self
     */
    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Sends the response and terminates execution.
     * Behavior depends on the active {@see ToastMode}:
     * - {@see ToastMode::FLASH}: issues a 302 redirect after storing the toast in the session.
     * - {@see ToastMode::DEFAULT}: sends the toast payload with the configured HTTP response code.
     * @throws GoralysRuntimeException If {@see toast()} or {@see error()} was not called beforehand.
     */
    #[NoReturn]
    public function send(): never
    {
        if (!$this->toast) {
            throw new GoralysRuntimeException(
                "Can not send null toast, if you meant to only send JSON data, please use an immadiate response instead"
            );
        }
        if ($this->context->mode === ToastMode::FLASH) {
            http_response_code(302);
            $this->controller->responder->sendToast($this->toast, $this->action ?? "", $this->data);
            error_log("KERNEL - 3: redirecting to: " . $this->toast->redirect);
            if ($this->context->client === Client::WEB) {
                header("Location: " . $this->toast->redirect);
            } elseif ($this->context->client === Client::MOBILE) {
                $this->json->send(["redirect" => $this->toast->redirect]);
            }
            exit;
        }
        http_response_code($this->code);
        $this->controller->responder->sendToast($this->toast, $this->action ?? "", $this->data);
        exit;
    }

    /**
     * Allows the response to carry extra JSON encoded data that is sent immediately to the frontend.
     * @param array|JsonSerializable $data The data to encode and send.
     * @return self
     */
    public function json(array|JsonSerializable $data): self
    {
        $this->data = $data;
        return $this;
    }
}
