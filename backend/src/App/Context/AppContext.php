<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Context;

use Goralys\App\Context\Data\Client;
use Goralys\App\Context\Data\ToastMode;
use JetBrains\PhpStorm\Deprecated;

/**
 * Holds the application-level context shared across the request lifecycle.
 */
final class AppContext
{
    /**
     * This variable is used to determine the context of the app in the global scope.
     * @var ?AppContext
     */
    public static ?self $CURRENT = null;


    /**
     * @param ToastMode $mode The current toast mode used by the app.
     * @param Client $client The current client fetching the backend.
     * @param bool $hasHighSchoolToken If the current request has a high school token.
     * This param is now deprecated, it will be removed soon when migration will be over.
     * Use isPublic instead.
     * @param ?string $originDomain The origin domain of the current request.
     * @param bool $isDev If the backend is currently running in a dev environment.
     * @param bool $isPublic If the current endpoint is publicly accessible without a valid high school token required.
     */
    public function __construct(
        public ToastMode $mode,
        public Client $client,
        #[Deprecated]
        public bool $hasHighSchoolToken,
        public ?string $originDomain,
        public bool $isDev,
        public bool $isPublic
    ) {
    }
}
