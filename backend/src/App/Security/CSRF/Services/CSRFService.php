<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Security\CSRF\Services;

use Goralys\App\Config\AppConfig;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use Random\RandomException;

/**
 * Service to manage the CSRF tokens system.
 */
final class CSRFService
{
    private LoggerInterface $logger;

    /**
     * Initializes the logger for the service.
     * @param LoggerInterface $logger The injected logger.
     */
    public function __construct(
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    /**
     * Gets the latest token for a given form.
     * @param string $formId The id of the form.
     * @return string The retrieved token.
     */
    public function getForForm(string $formId): string
    {
        $tokens = $_SESSION["csrf-tokens-table"][$formId] ?? [];
        return $tokens ? end($tokens) : "";
    }

    /**
     * Creates a new CSRF token.
     * @param string $formId The id of the form to create the token for.
     * @return bool If the creation was successful or not.
     */
    public function create(string $formId): bool
    {
        try {
            $token = bin2hex(random_bytes(AppConfig::CSRF_TOKENS_SIZE));
            $_SESSION["csrf-tokens-table"][$formId] ??= [];
            $_SESSION["csrf-tokens-table"][$formId][] = $token;

            if (count($_SESSION["csrf-tokens-table"][$formId]) > AppConfig::MAX_CSRF_TOKENS) {
                array_shift($_SESSION["csrf-tokens-table"][$formId]);
            }

            return true;
        } catch (RandomException $e) {
            $this->logger->error(
                LoggerInitiator::APP,
                "An error occurred while generating a CSRF token for form : " . $formId . "\nError:" . $e->getMessage(),
            );
            return false;
        }
    }

    /**
     * Validates a given CSRF token for a specific form.
     * @param string $formId The id of the form to verify the token for.
     * @param RequestInterface $request The current HTTP request.
     * @return bool If the token is valid or not.
     */
    public function validate(string $formId, RequestInterface $request): bool
    {
        $token = $request->param('csrf-token');

        if (!isset($_SESSION["csrf-tokens-table"][$formId])) {
            $this->logger->error(
                LoggerInitiator::APP,
                "Foreign token form id encountered : " . $formId,
            );
            return false;
        }

        if (!in_array($token, $_SESSION["csrf-tokens-table"][$formId])) {
            $this->logger->error(
                LoggerInitiator::APP,
                "Failed to validate token for form : " . $formId . "(" . $token . ")",
            );
            return false;
        }

        $k = array_search($token, $_SESSION['csrf-tokens-table'][$formId]);
        unset($_SESSION["csrf-tokens-table"][$formId][$k]);
        return true;
    }
}
