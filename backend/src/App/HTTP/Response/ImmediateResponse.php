<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Response;

use Goralys\App\Context\AppContext;
use Goralys\App\Context\Data\Client;
use Goralys\App\HTTP\Files\Interface\FileResponder;
use Goralys\App\HTTP\JSON\Interfaces\JsonResponder;
use Goralys\App\HTTP\Response\Interfaces\ImmediateResponseInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;
use Throwable;

/**
 * An immediate HTTP response that supports JSON and file downloading.
 *
 * Unlike {@see DeferredResponse} an immediate response is sent immediatly.
 */
final class ImmediateResponse implements ImmediateResponseInterface
{
    private int $code;
    private LoggerInterface $logger;
    private FileResponder $files;
    private JsonResponder $json;
    private AppContext $context;

    /**
     * @param int $code The HTTP code of the response.
     * @param LoggerInterface $logger The injected logger.
     * @param FileResponder $files The injected FileResponder service.
     * @param JsonResponder $json The injected JsonResponder service.
     * @param AppContext $context The injected application context.
     */
    public function __construct(
        int $code,
        LoggerInterface $logger,
        FileResponder $files,
        JsonResponder $json,
        AppContext $context
    ) {
        $this->code = $code;
        $this->logger = $logger;
        $this->files = $files;
        $this->json = $json;
        $this->context = $context;
    }

    /**
     * Send a downloadable file response to the client.
     * @param string $path The path of the file to download.
     * @param string $name The name of the when downloaded by the client.
     * @param callable|null $after An optionnal callback to run after the download.
     * @throws Throwable If the download fails.
     */
    #[NoReturn]
    public function download(string $path, string $name, ?callable $after = null): never
    {
        try {
            $this->files->send($path, $name);
            $this->logger->info(LoggerInitiator::APP, "File $path was successfully downloaded");
            try {
                ($after ?? fn() => null)();
            } catch (Throwable $e) {
                $this->logger->warning(
                    LoggerInitiator::APP,
                    "Post response callback failed. \n Error: " . $e->getMessage(),
                );
            }
            http_response_code($this->code);
            exit;
        } catch (Throwable $e) {
            $this->logger->error(LoggerInitiator::APP, "Failed to download file: $path\n Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sends JSON data to the client.
     * @param array|JsonSerializable $data The data to send to the client.
     * @param callable|null $after An optionnal callback to run after the data was sent.
     * @throws Throwable If the JSON data cannot be sent.
     */
    #[NoReturn]
    public function json(array|JsonSerializable $data, ?callable $after = null): never
    {
        try {
            $json = json_encode($data);
            $size = $json !== false ? strlen($json) : 0;

            $this->json->send($data);
            $this->logger->info(LoggerInitiator::APP, "Successfully sent JSON (size: $size)");
            if ($size < 250) {
                $this->logger->debug(LoggerInitiator::APP, print_r($json, true));
            }
            try {
                ($after ?? fn() => null)();
            } catch (Throwable $e) {
                $this->logger->warning(
                    LoggerInitiator::APP,
                    "Post response callback failed. \n Error: " . $e->getMessage(),
                );
            }
            http_response_code($this->code);
            exit;
        } catch (Throwable $e) {
            $this->logger->error(
                LoggerInitiator::APP,
                "Failed to send JSON (size: $size)\n Error: " . $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * Send a simple HTTP response to the client.
     * @return never
     */
    #[NoReturn]
    public function http(): never
    {
        http_response_code($this->code);
        exit;
    }

    #[NoReturn]
    /**
     * Send a simple redirect to the client.
     * @param string $dest The url to redirect the client to.
     * @return never
     */
    public function redirect(string $dest): never
    {
        if ($this->context->client === Client::WEB) {
            header("Location: " . $dest);
        } elseif ($this->context->client === Client::MOBILE) {
            $this->json->send(["redirect" => $dest]);
        }
        $this->logger->debug(LoggerInitiator::APP, "Redirected client to: " . $dest);
        exit;
    }
}
