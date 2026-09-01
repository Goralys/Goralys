<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Middleware;

use Goralys\Kernel\GoralysKernel;
use Throwable;

/**
 * Middleware that opens the database connection before a route handler runs.
 * Optionally wraps the handler in a database transaction that is committed on success or rolled back on exception.
 */
final class DbMiddleware implements Interface\MiddlewareInterface
{
    private const string TRANSACTION = 'transaction';
    private array $options;

    /**
     * @param string $route The matched route path.
     * @param mixed ...$params Accepts the 'transaction' flag to enable transaction wrapping.
     */
    public function __construct(string $route, ...$params)
    {
        $this->options = $params;
    }

    /**
     * Opens the DB connection and wraps the callback in a transaction if the 'transaction' flag was passed.
     * @param GoralysKernel $kernel The application kernel.
     * @param callable $next The next handler in the pipeline.
     * @return mixed
     * @throws Throwable If the transaction catches any exception.
     */
    public function handle(GoralysKernel $kernel, callable $next): mixed
    {
        $kernel->requireDb();
        $transaction = in_array(self::TRANSACTION, $this->options, true);

        if (!$transaction) {
            return $next($kernel);
        }

        try {
            $kernel->db->beginTransaction();
            register_shutdown_function(fn() => $kernel->db->commit());

            $result = $next($kernel);

            $kernel->db->commit();

            return $result;
        } catch (Throwable $e) {
            $kernel->db->rollback();
            throw $e;
        }
    }

    /**
     * Returns the middleware binding that only opens the database connection.
     * @return array The middleware descriptor array.
     */
    public static function require(): array
    {
        return ['db'];
    }

    /**
     * Returns the middleware binding that opens the database connection and wraps the handler in a transaction.
     * @return array The middleware descriptor array.
     */
    public static function transaction(): array
    {
        return ['db', [self::TRANSACTION]];
    }
}
