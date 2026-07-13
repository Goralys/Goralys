<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\DB\Interfaces;

use mysqli_result;

/**
 * Contract for the database container.
 * Covers connection, query execution, and transaction management.
 */
interface DbContainerInterface
{
    /**
     * Establishes the connection to the database.
     * @param string $dbName The name of the database to connect to.
     * @return bool Whether the connection was successfully established.
     */
    public function connect(string $dbName): bool;

    /* Transactions */

    /**
     * Begins a new database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Rolls back the current transaction.
     */
    public function rollback(): void;

    /**
     * Commits the current transaction.
     */
    public function commit(): void;

    /* Queries */

    /**
     * Executes a prepared select-query and returns the result set.
     * @param string $query The SQL query with placeholders.
     * @param string $types The bind types string (e.g. `"si"` for string + int).
     * @param mixed $value1 The first bound value.
     * @param mixed ...$args Additional bound values.
     * @return mysqli_result The query result set.
     */
    public function fetch(string $query, string $types, mixed $value1, mixed ...$args): mysqli_result;

    /**
     * Executes a select-query with no bound parameters and returns the result set.
     * @param string $query The SQL query to execute.
     * @return mysqli_result The query result set.
     */
    public function fetchNoArgs(string $query): mysqli_result;

    /**
     * Executes a prepared write-query (INSERT, UPDATE, DELETE) and returns its outcome.
     * @param string $query The SQL query with placeholders.
     * @param string $types The bind types string (e.g. `"si"` for string + int).
     * @param mixed $value1 The first bound value.
     * @param mixed ...$args Additional bound values.
     * @return bool Whether the query was executed successfully.
     */
    public function run(string $query, string $types, mixed $value1, mixed ...$args): bool;

    /**
     * Executes a write-query with no bound parameters and returns its outcome.
     * @param string $query The SQL query to execute.
     * @return bool Whether the query was executed successfully.
     */
    public function runNoArgs(string $query): bool;
}
