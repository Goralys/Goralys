<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\Kernel\GoralysKernel;
use Goralys\Shared\Config\GoralysConfig;

// ----------- API bootstrap method ---------- //
/**
 * Sets CORS headers, handles OPTIONS preflight requests, and validates the session user-agent.
 * Also triggers session ID regeneration every 15 minutes for active sessions.
 * @param GoralysKernel $kernel The initialized application kernel.
 * @return void
 */
function bootstrapAPI(GoralysKernel $kernel): void
{
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }

    error_log("BOOTSTRAP - 1: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $forwardedOrigin = $_SERVER['HTTP_X_FORWARDED_ORIGIN'] ?? '';

    $effectiveOrigin = $origin !== '' ? $origin : $forwardedOrigin;
    $allowed = array_map('trim', explode(",", $kernel->env->getByKey("ALLOWED_DOMAINS")));

    if (in_array($effectiveOrigin, $allowed)) {
        header("Access-Control-Allow-Origin: $effectiveOrigin");
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH, PUT, BREW, WHEN');
    header('Access-Control-Max-Age: 86400'); // 1 day
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method-Override');

    // Preflight requests
    error_log("BOOTSTRAP - 2: preflight check, method=" . $_SERVER['REQUEST_METHOD']);
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204); // No content, but OK
        exit;
    }

    // Check if the user agent from the client is valid
    error_log("BOOTSTRAP - 3: UA check, current_id=" . ($_SESSION[GoralysConfig::SESSION::ID] ?? 'none')
            . ", UA=" . ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
    if (isset($_SESSION[GoralysConfig::SESSION::ID])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if ($userAgent !== null) {
            $ua = $_SESSION['ua'] ?? null;
            $uaHash = hash("sha256", $userAgent);

            if (!$ua || $uaHash !== $ua) {
                session_unset();
                session_destroy();
                http_response_code(401);
                exit;
            }
        }
        error_log("BOOTSTRAP - 4: Regen check "
                . (!isset($_SESSION['regen_time']) || time() - $_SESSION['regen_time'] > 900 ? 'true' : 'false'));
        if (!isset($_SESSION['regen_time']) || time() - $_SESSION['regen_time'] > 900) {
            session_regenerate_id(true);
            $_SESSION['regen_time'] = time();
        }
    }

    error_log("BOOTSTRAP - 5: Done");
}

// --------------- Kernel Init --------------- //
/**
 * Creates, configures, and bootstraps the application kernel.
 * @return GoralysKernel The fully initialized kernel.
 */
function bootKernel(): GoralysKernel
{
    $kernel = new GoralysKernel(__DIR__ . "/../../");
    $kernel->setHandlers();
    bootstrapAPI($kernel);
    return $kernel;
}
