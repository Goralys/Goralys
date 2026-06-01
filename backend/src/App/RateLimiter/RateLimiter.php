<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\RateLimiter;

use Goralys\App\Config\AppConfig;
use Goralys\App\Config\Data\RateLimitTimeMethod;
use Goralys\App\Config\RateLimiterConfig;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Config\GoralysConfig;
use Random\RandomException;

/**
 * File-based rate limiter that tracks request counts per IP address.
 * Supports constant, linear, and exponential back-off penalty windows.
 */
final class RateLimiter
{
    private const string ALGO = "sha256";
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger The injected logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $rate = RateLimiterConfig::getRateLimits()[$endpoint] ?? null;

        $filename = AppConfig::BASE_STORAGE_DIR
            . "RateLimiter/"
            . hash(self::ALGO, $endpoint)
            . ".txt";

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0o777, true);
        }

        if (!$token = $this->resolveToken()) {
            return false;
        }

        $fp = fopen($filename, 'c+');
        if (!$fp) {
            return true;
        }

        flock($fp, LOCK_EX);

        rewind($fp);
        $contents = stream_get_contents($fp);
        $data = json_decode($contents ?: '[]', true);

        if (!is_array($data)) {
            $data = [];
        }

        // init
        if (!isset($data[$token])) {
            $data[$token] = [
                'count' => 0,
                'reset_time' => 0,
                'failures' => 0,
            ];
        }

        $now = time();
        $limit = $rate?->maxRequests ?? RateLimiterConfig::GENERAL[0];
        $period = $rate?->timeWindowSeconds ?? RateLimiterConfig::GENERAL[1];

        $n = min(
            $rate?->maxLevels ?? 1,
            $data[$token]['failures'] ?? 0,
        );

        $timeMethod = $rate?->timeMethod ?? RateLimitTimeMethod::CONSTANT;

        $penalty = match ($timeMethod) {
            RateLimitTimeMethod::CONSTANT => $period,
            RateLimitTimeMethod::LINEAR => $period * $n,
            RateLimitTimeMethod::EXPONENTIAL => min($period * (2 ** $n), 3600), // 1 hour max
        };

        $this->logger->debug(LoggerInitiator::APP, "Penalty for $endpoint(max: $rate?->maxLevels): $penalty");

        if ($data[$token]['reset_time'] <= $now) {
            $data[$token]['count'] = 0;
            $data[$token]['reset_time'] = 0;
        }

        // limit check
        if ($data[$token]['count'] >= $limit) {
            $data[$token]['failures'] = min(
                $data[$token]['failures'] + 1,
                $rate?->maxLevels ?? 1,
            );
            $data[$token]['reset_time'] = $now + $penalty;
            $this->finalWrite($fp, json_encode($data));
            return false;
        }

        $data[$token]['count']++;
        $data[$token]['reset_time'] = $now + $penalty;
        // clean up old entries
        foreach ($data as $tokenKey => $entry) {
            if (($entry['reset_time'] ?? 0) < $now) {
                unset($data[$tokenKey]);
            }
        }

        $this->finalWrite($fp, json_encode($data));
        return true;
    }

    /**
     * Resolves a unique token for rate limiting. This includes the generation of a unique rate limiting token,
     * if the random generation fails, the system falls back to ip only.
     * @return ?string The token that identifies the current client.
     */
    private function resolveToken(): ?string
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                $this->logger->warning(LoggerInitiator::APP, "Invalid IP address encountered: $ip");
                return null;
            }

            $token = $_COOKIE[GoralysConfig::COOKIES::RATE_LIMITER_TOKEN] ?? null;

            if (!$token) {
                $token = bin2hex(random_bytes(16));
                setcookie(GoralysConfig::COOKIES::RATE_LIMITER_TOKEN, $token, [
                    'expires' => time() + 86400 * 30,
                    'httponly' => true,
                    'secure' => true,
                    'samesite' => 'Strict',
                ]);
            }

            return "$ip:$token";
        } catch (RandomException) {
            return $_SERVER['REMOTE_ADDR'] ?? null;
        }
    }

    /**
     * Atomically writes `$data` to the file, truncating any previous content, then releases the lock.
     * @param resource $f The locked file handle.
     * @param string $data The serialized data to persist.
     * @return void
     */
    private function finalWrite($f, string $data): void
    {
        rewind($f);
        ftruncate($f, 0);
        fwrite($f, $data);
        flock($f, LOCK_UN);
        fclose($f);
    }
}
