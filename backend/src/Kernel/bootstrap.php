<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Loader\Services\EnvService;
use Goralys\Platform\Loader\Services\HighSchoolsService;
use Goralys\Shared\Config\GoralysConfig;

function isPublic(string $uri): bool
{
    $publicPrefixes = ['/highschools'];
    return array_any($publicPrefixes, fn($prefix) => str_starts_with($uri, $prefix));
}

// loads the .env file into the environment before the kernel is initialized
function loadPreBootEnv(): EnvService
{
    $envPath = __DIR__ . "/../../";
    $envService = new EnvService();
    $success = $envService->load($envPath);

    error_log("ENV - load success=" . ($success ? 'true' : 'false') . ", path=" . $envPath);
    error_log("ENV - GORALYS_ENVIRONMENT after load: " . ($_ENV['GORALYS_ENVIRONMENT'] ?? 'STILL UNDEFINED'));

    return $envService;
}

// ----------- API bootstrap method ---------- //

/**
 * Checks whether a given origin is allowed to talk to the API.
 * Relies solely on MASTER_DOMAIN (and the ALLOWED_DOMAINS fallback) — never on the
 * high-school-token, since preflight (OPTIONS) requests never carry it.
 * @param string $origin The Origin header sent by the browser.
 * @param EnvService $env The loaded environment variables.
 * @return bool
 */
function isAllowedOrigin(string $origin, EnvService $env): bool
{
    if ($origin === '') {
        return false;
    }

    $masterDomain = $env->getByKey("MASTER_DOMAIN") ?: '';

    if ($masterDomain !== '') {
        $escapedDomain = preg_quote($masterDomain, '/');

        // http(s)://[sous-domaine.]master-domain[:port]
        $pattern = '/^https?:\/\/([a-z0-9]+\.)?' . $escapedDomain . '(:\d+)?$/i';

        if (preg_match($pattern, $origin)) {
            return true;
        }
    }

    // Fallback
    $extra = array_map('trim', explode(",", $env->getByKey("ALLOWED_DOMAINS") ?: ''));
    return in_array($origin, $extra, true);
}

/**
 * Sets CORS headers based on the request Origin.
 * Must run before any token/kernel resolution, so that preflight (OPTIONS) requests —
 * which never carry the high-school-token — still get a valid CORS response.
 * @param EnvService $env The loaded environment variables.
 * @return void
 */
function setCorsHeaders(EnvService $env): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_X_FORWARDED_ORIGIN'] ?? '');

    error_log("CORS - 1: request origin=" . ($origin !== '' ? $origin : 'none'));

    if (isAllowedOrigin($origin, $env)) {
        error_log("CORS - 2: origin ALLOWED");
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    } else {
        error_log("CORS - 2: origin REJECTED");
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH, PUT, BREW, WHEN');
    header('Access-Control-Max-Age: 86400'); // 1 day
    header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, Cache-Control, Pragma, Expires,'
            . 'X-Requested-With, X-HTTP-Method-Override, X-High-School-Token');

    error_log("CORS - 3: headers set (unconditional Allow-Methods/Allow-Headers included)");
}

/**
 * Short-circuits OPTIONS preflight requests with a 204.
 * Must run AFTER setCorsHeaders(), so the preflight response actually carries the CORS headers.
 * @return void
 */
function handlePreflight(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        error_log("CORS - 4: OPTIONS preflight, responding 204");
        http_response_code(204); // No content, but OK
        exit;
    }
}

/**
 * Reads the high-school-token from either the X-High-School-Token header (used by fetch-based
 * calls) or the high-school-token query param (fallback for native form navigations, which
 * cannot set custom headers).
 * @return ?string
 */
function getHighSchoolToken(): ?string
{
    $token = $_SERVER['HTTP_X_HIGH_SCHOOL_TOKEN'] ?? $_GET['high-school-token'] ?? null;
    error_log("KERNEL - 1: token=" . ($token ?? 'none'));
    return $token;
}

/**
 * Resolves the origin domain (front URL) for a given high-school-token.
 * Lightweight: only reads the lycees.ini file, no DB connection.
 * @param ?string $token
 * @return ?string
 */
function resolveOriginDomain(?string $token): ?string
{
    if ($token === null) {
        error_log("KERNEL - 2: no token provided, origin resolution skipped");
        return null;
    }

    $schools = new HighSchoolsService();
    $domain = $schools->getDomainForSchool($token);

    error_log("KERNEL - 3: resolved domain=" . ($domain ?? 'null (unknown token)'));

    return $domain;
}

/**
 * Validates the session user-agent and triggers session ID regeneration every 15 minutes for
 * active sessions. CORS is handled earlier, in bootKernel(), before the kernel is constructed.
 * @param GoralysKernel $kernel The initialized application kernel.
 * @return void
 */
function bootstrapAPI(GoralysKernel $kernel): void
{
    date_default_timezone_set('Europe/Paris');  // change if you are not french
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }

    error_log("BOOTSTRAP - 1: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

    // Check if the user agent from the client is valid
    error_log("BOOTSTRAP - 2: UA check, current_id=" . ($_SESSION[GoralysConfig::SESSION::ID] ?? 'none')
        . ", UA=" . ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
    $whiteListUA = ["node"];
    if (isset($_SESSION[GoralysConfig::SESSION::ID])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $shouldCheck = true;

        foreach ($whiteListUA as $uaPattern) {
            if (str_starts_with($userAgent, $uaPattern)) {
                $shouldCheck = false;
                error_log("Skipping check for UA: " . $userAgent);
                break;
            }
        }

        if ($userAgent !== null && $shouldCheck) {
            $ua = $_SESSION['ua'] ?? null;
            $uaHash = hash("sha256", $userAgent);

            if (!$ua || $uaHash !== $ua) {
                session_unset();
                session_destroy();
                $kernel->response(401)->http();
            }
        }
        error_log("BOOTSTRAP - 3: Regen check "
            . (!isset($_SESSION['regen_time']) || time() - $_SESSION['regen_time'] > $kernel->getSessionLifetime()
            ? 'true' : 'false'));
        if (!isset($_SESSION['regen_time']) || time() - $_SESSION['regen_time'] > $kernel->getSessionLifetime()) {
            session_regenerate_id(true);
            $_SESSION['regen_time'] = time();
        }
    }

    error_log("BOOTSTRAP - 4: Done");
}

// --------------- Kernel Init --------------- //
/**
 * Creates, configures, and bootstraps the application kernel.
 * @return GoralysKernel The fully initialized kernel.
 */
function bootKernel(): GoralysKernel
{
    $env = loadPreBootEnv();
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    error_log("KERNEL - 0: bootKernel start, method=" . ($_SERVER['REQUEST_METHOD'] ?? 'unknown')
        . ", uri=" . ($_SERVER['REQUEST_URI'] ?? 'unknown'));

    if (isPublic($uri)) {
        error_log("KERNEL - 1: public resource, skipping token checks");
        setCorsHeaders($env);
        handlePreflight();
        $kernel = new GoralysKernel(__DIR__ . "/../../", skipHighSchoolToken: true);
        $kernel->setHandlers();
        bootstrapAPI($kernel);
        return $kernel;
    }

    setCorsHeaders($env);
    handlePreflight();

    $token = getHighSchoolToken();
    $resolvedOrigin = resolveOriginDomain($token);

    if ($resolvedOrigin === null) {
        error_log("KERNEL - 4: aborting, missing or invalid token");
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing or invalid high-school-token"]);
        exit;
    }

    error_log("KERNEL - 5: constructing GoralysKernel");
    $kernel = new GoralysKernel(__DIR__ . "/../../");
    $kernel->setHandlers();
    bootstrapAPI($kernel);
    error_log("KERNEL - 6: boot complete");
    return $kernel;
}
