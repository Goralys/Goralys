<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\Config;

/**
 * Global application configuration constants.
 */
final class AppConfig
{
    public const int MAX_DRAFT_SIZE = 50 * 1024;

    public const int CSRF_TOKENS_SIZE = 32;
    public const int MAX_CSRF_TOKENS = 3;

    public const string BASE_STORAGE_DIR = __DIR__ . "/../../../";

    /* @var string[] */
    public const array NAME_PARTICULES = [
        // French
        'DE', 'DU', 'DES', 'LE', 'LA', 'LES', 'L', 'D', 'SAINT',

        // Dutch / Flemish
        'VAN', 'TEN', 'TER', 'TE', 'DEN', 'DER', 'IN', 'OP',

        // German
        'VON', 'ZU', 'ZUM', 'ZUR', 'VOM', 'AUF',

        // Italian
        'DI', 'DA', 'DAL', 'DALL', 'DALLA', 'DALLE', 'DAGLI', 'DEL', 'DELL',
        'DELLA', 'DELLE', 'DEGLI', 'DEI', 'LO', 'LI',

        // Spanish / Portuguese
        'DEL', 'LOS', 'LAS', 'DO', 'DOS', 'DA', 'DAS', 'E',

        // Arabic
        'AL', 'EL', 'BEN', 'BIN', 'BINT', 'ABU', 'IBN', 'OU', 'OUL',

        // Irish / Scottish
        'O', 'MAC', 'MC', 'FITZ', 'NI', 'NIC', 'UA',

        // Scandinavian
        'AF', 'AV',

        // Others
        'SAN', 'SANTA', 'SANTO', 'DOS',
    ];
}
