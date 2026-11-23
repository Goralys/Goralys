<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\RateLimiter;

use Goralys\App\Config\Data\RateLimit;
use Goralys\App\Config\Data\RateLimitTimeMethod;
use Goralys\App\Config\RateLimiterConfig;
use Goralys\App\Context\Data\CurrentSchool;
use Goralys\App\Cookies\Cookies;
use Goralys\App\RateLimiter\Data\ClientToken;
use Goralys\Core\RateLimiter\Repository\Interfaces\RateLimiterRepositoryInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig as Config;
use Random\RandomException;

/**
 * File-based rate limiter that tracks request counts per IP address.
 * Supports constant, linear, and exponential back-off penalty windows.
 */
final class RateLimiter
{
    private const string ALGO = "sha256";
    private const int PURE_MULTIPLIER = 5;

    private static bool $IS_ACCOUNTED = true;
    private LoggerInterface $logger;
    private RateLimiterRepositoryInterface $repo;
    private bool $disabled = false;

    /**
     * @param LoggerInterface $logger The injected logger.
     * @param RateLimiterRepositoryInterface $repo The injected repository.
     */
    public function __construct(LoggerInterface $logger, RateLimiterRepositoryInterface $repo)
    {
        $this->logger = $logger;
        $this->repo = $repo;
    }

    /**
     * Disables the rate limiter for the current request.
     * @return void
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * This function makes the current request unaccounted for rate limiting.
     * @return void
     */
    public static function unaccounted(): void
    {
        self::$IS_ACCOUNTED = false;
    }

    /**
     * Checks whether the current request from the client's IP is within the rate limit for the given endpoint.
     * Increments the counter and updates the penalty window on each call.
     * Falls back to the general limit defined in {@see RateLimiterConfig::GENERAL} if no per-endpoint rule exists.
     * @param string $endpoint The name of the endpoint to check
     * (must match a key in {@see RateLimiterConfig::getRateLimits()}).
     * @return bool True if the request is allowed, false if the rate limit has been exceeded.
     */
    public function forwardRequest(string $endpoint): bool
    {
        if ($this->disabled) {
            return true;
        }

        $rate = RateLimiterConfig::getRateLimits()[$endpoint] ?? new RateLimit(...RateLimiterConfig::GENERAL);
        $limit = $rate->maxRequests;
        $period = $rate->timeWindowSeconds;

        $token = $this->resolveToken();
        $this->repo->select($token->token, $endpoint);
        $this->repo->increment();

        if (!self::checkToken($token)) {
            return false;
        }

        $info = $this->repo->getInfo();
        $count = $info->count;
        $failures = (int) floor($info->count / $limit);

        if ($info->banFor > 0) {
            // do not count requests during a ban
            $this->repo->increment(-1);
            return false;
        }

        if ($info->sinceLastZero > $period) {
            $count = 1 + max(0, $failures - 1) * $limit;
            $failures = max(0, $failures - 1);
            $this->repo->set($count, refreshZero: true);
        }

        register_shutdown_function(function () use ($token, $endpoint, $count, $limit, $rate, $period, $failures) {
            // We only write the new data if the request should be accounted for rate limiting.
            // For example, a successful login request is not accounted for.
            $this->repo->select($token->token, $endpoint);
            if (!self::$IS_ACCOUNTED) {
                $this->repo->increment(-1);
            } else {
                if ($count % $limit === 0 && $count > 0) {
                    $n = min($rate->maxLevels, $failures);
                    $penalty = match ($rate->timeMethod) {
                        RateLimitTimeMethod::CONSTANT => $period,
                        RateLimitTimeMethod::LINEAR => $period * $n,
                        RateLimitTimeMethod::EXPONENTIAL => min($period * (2 ** ($n - 1)), 3600), // 1 hour max
                    };

                    $this->logger->debug(
                        LoggerInitiator::APP,
                        "Penalty for $endpoint(max: $rate->maxLevels): $penalty"
                    );
                    $this->repo->ban($penalty);
                }
            }
        });

        return true;
    }

    /**
     * Resolves a unique token for rate limiting. This includes the generation of a unique rate limiting token,
     * if the random generation fails, the system falls back to ip only.
     * @return ?ClientToken The token that identifies the current client.
     */
    private function resolveToken(): ?ClientToken
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                $this->logger->warning(LoggerInitiator::APP, "Invalid IP address encountered: $ip");
                return null;
            }

            $token = $_COOKIE[Config::COOKIES::RATE_LIMITER_TOKEN] ?? null;
            if (!$token) {
                $base = bin2hex(random_bytes(4));
                $token = hash_hmac(self::ALGO, CurrentSchool::$CODE . $base, CurrentSchool::$SECRET) . ">>$base";
                Cookies::set(Config::COOKIES::RATE_LIMITER_TOKEN, $token, 3600 * 24 * 30);
            }

            return new ClientToken("$ip>>$token", $ip, false);
        } catch (RandomException) {
            return !!$ip ? new ClientToken($ip, $ip, true) : null;
        }
    }

    /**
     * Checks if a given token is valid.
     * @param ClientToken $ct The token to check.
     * @return bool If the token is valid.
     */
    private static function checkToken(ClientToken $ct): bool
    {
        if ($ct->isPure) {
            return false;
        }

        $t = $ct->token;
        // extract token (format: ip>>token>>id)
        $data = explode(">>", $t);
        if (count($data) !== 3) {
            return false;
        }

        [, $hash, $id] = $data;
        $check = hash_hmac(self::ALGO, CurrentSchool::$CODE . $id, CurrentSchool::$SECRET);
        return hash_equals($check, $hash);
    }
}
