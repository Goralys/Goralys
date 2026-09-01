<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\User\Data\Enums;

/**
 * This enum is used to represent the status of the user's authentication.
 * There are three statuses possible:
 * - {@see UserAuthStatus::AUTHENTICATED} When the user is already authenticated.
 * - {@see UserAuthStatus::SESSION_EXPIRED} When the user's session has expired (1 hour by default).
 * - {@see UserAuthStatus::NOT_AUTHENTICATED} When the user is not authenticated or has not been active for
 * 2 hours (2x session lifetime).
 */
enum UserAuthStatus: int
{
    case AUTHENTICATED = 1;
    case SESSION_EXPIRED = 0;
    case NOT_AUTHENTICATED = -1;
}
