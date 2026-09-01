<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Context\Data;

use Goralys\App\HTTP\Request\Interfaces\RequestInterface;

/**
 * Defines how redirects are delivered to the frontend:
 * WEB can send redirects via headers,
 * MOBILE sends it inline as a JSON response.
 */
enum Client
{
    case WEB;
    case MOBILE;

    /**
     * Determines the client from the request.
     * If the required header is not present, defaults to {@see Client::WEB}.
     * @param RequestInterface $request The current incoming request.
     * @return self
     */
    public static function fromRequest(RequestInterface $request): self
    {
        return match ($request->header('X-Goralys-Client')) {
            'mobile' => self::MOBILE,
            default => self::WEB,
        };
    }
}
