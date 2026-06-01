<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Response\Interfaces;

use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;

/**
 * A contract used to represent an immediate response.
 */
interface ImmediateResponseInterface
{
    /**
     * Sends a simple http response.
     * @return never
     */
    #[NoReturn]
    public function http(): never;

    /**
     * Sends a downloadable file response to the client.
     * @param string $path The path of the file to download.
     * @param string $name The name of the file when downloaded by the client.
     * @param callable|null $after An optionnal callback to run after the download.
     * @return never
     */
    #[NoReturn]
    public function download(
        string $path,
        string $name,
        ?callable $after = null,
    ): never;

    /**
     * Sends JSON data to the client.
     * @param array|JsonSerializable $data The data to send.
     * @param callable|null $after An optionnal callback to run after the data was sent.
     * @return never
     */
    #[NoReturn]
    public function json(array|JsonSerializable $data, ?callable $after = null): never;

    /**
     * Sends a redirect to the client.
     * @param string $dest The url to redirect the user to.
     * @return never
     */
    #[NoReturn]
    public function redirect(string $dest): never;
}
