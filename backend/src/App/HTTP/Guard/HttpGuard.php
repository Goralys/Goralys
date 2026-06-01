<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Guard;

use Goralys\App\Context\AppContext;
use Goralys\App\HTTP\Guard\Interface\GuardInterface;
use Goralys\App\HTTP\Request\Interfaces\RequestInterface;
use Goralys\App\HTTP\Response\DeferredResponse;
use Goralys\App\HTTP\Response\Interfaces\DeferredResponseInterface;
use Goralys\App\Utils\Toast\Data\Enums\ToastType;
use Goralys\Core\User\Services\UsernameManager;
use Goralys\Shared\Config\GoralysConfig;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * HTTP implementation of the guard that validates request fields against the session user.
 */
final class HttpGuard implements GuardInterface
{
    private AppContext $context;
    private UsernameManager $usernameManager;

    /**
     * @param UsernameManager $usernameManager The injected username manager.
     * @param AppContext $context The injected application context.
     */
    public function __construct(UsernameManager $usernameManager, AppContext $context)
    {
        $this->usernameManager = $usernameManager;
        $this->context = $context;
    }

    /**
     * Returns a 403 deferred response if the request field does not match the session username.
     * Accepts both the raw username and its public token as valid values.
     * @param RequestInterface $request The current HTTP request.
     * @param string $field The request field to compare against the session username.
     * @return DeferredResponseInterface|null Null on success, a 403 response on mismatch.
     * @throws GoralysRuntimeException If the user's public id cannot be retrieved.
     */
    public function matchCurrentUser(RequestInterface $request, string $field): ?DeferredResponseInterface
    {
        $actual = $_SESSION[GoralysConfig::SESSION::USERNAME] ?? null;
        $given = $request->param($field);
        if ($actual && ($given == $actual || $this->usernameManager->get($given) == $actual)) {
            return null;
        }

        return new DeferredResponse($this->context, 403)->toast( // Unauthorized
            ToastType::WARNING,
            "Mauvais utilisateur",
            "Il semblerait que vous ne soyez pas le bon utilisateur",
        );
    }
}
