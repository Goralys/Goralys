<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\JSON\Services;

use Goralys\App\HTTP\JSON\Interfaces\JsonResponder;
use Goralys\Shared\Exception\GoralysRuntimeException;
use JsonSerializable;

/**
 * The HTTP service used to send JSON to the client.
 */
final class HttpJsonResponder implements JsonResponder
{
    /**
     * Send the given data in the shape of a JSON response to the client.
     * @param array|JsonSerializable $data The data to send.
     * @param int $responseCode The HTTP code of the response to send.
     * @return void
     */
    public function send(array|JsonSerializable $data, int $responseCode = 200): void
    {
        if (!headers_sent()) {
            header("Content-Type: application/JSON; charset: utf-8");
        }

        http_response_code($responseCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
