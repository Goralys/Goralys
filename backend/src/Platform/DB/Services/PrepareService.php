<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Platform\DB\Services;

use Goralys\Platform\DB\Data\StmtDto;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Goralys\Shared\Exception\DB\GoralysPrepareException;
use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;

/**
 * The service used to prepare statements.
 */
final class PrepareService
{
    private LoggerInterface $logger;
    private mysqli $conn;

    public function __construct(
        LoggerInterface $logger,
        mysqli $conn,
    ) {
        $this->logger = $logger;
        $this->conn = $conn;
    }

    /**
     * Prepare a statement and returns it.
     * Handles and log any error that could occur during preparation.
     * @param StmtDto $stmtData The necessary data to prepare the statement.
     * @return mysqli_stmt The prepared statement.
     * @throws GoralysPrepareException If the preparation fails.
     */
    public function prepareAndBind(StmtDto $stmtData): mysqli_stmt
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $cTypes = strlen($stmtData->types);
        $cArgs = count($stmtData->args);
        if ($cTypes !== $cArgs) {
            $this->logger->error(
                LoggerInitiator::PLATFORM,
                "Invalid param count for statement with query (expected $cTypes, got $cArgs) : " . $stmtData->query,
            );
            throw new GoralysPrepareException("Failed to prepare statement.");
        }

        try {
            $stmt = $this->conn->prepare($stmtData->query);

            $args = $stmtData->args;
            $refs = [];

            foreach ($args as &$a) {
                if ($a instanceof \DateTime) {
                    $a = $a->format('Y-m-d H:i:s');
                }
                $refs[] = &$a;
            }

            $stmt->bind_param(
                $stmtData->types,
                ...$refs,
            );
        } catch (mysqli_sql_exception $e) {
            $this->logger->error(
                LoggerInitiator::PLATFORM,
                "An error occurred while preparing statement with query : " . $stmtData->query
                . ". Error : " . $e->getMessage(),
            );
            throw new GoralysPrepareException("Failed to prepare statement.");
        }
        return $stmt;
    }

    /**
     * Prepares a statement without parameters.
     * @param string $query The statement's request.
     * @return mysqli_stmt The prepared statement.
     * @throws GoralysPrepareException If the preparation fails.
     */
    public function prepare(string $query): mysqli_stmt
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $stmt = $this->conn->prepare($query);
        } catch (mysqli_sql_exception $e) {
            $this->logger->error(
                LoggerInitiator::PLATFORM,
                "An error occurred while preparing statement with query : " . $query
                    . ". Error : " . $e->getMessage(),
            );
            throw new GoralysPrepareException("Failed to prepare statement.");
        }
        return $stmt;
    }
}
