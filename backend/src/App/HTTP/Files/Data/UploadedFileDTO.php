<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Files\Data;

/**
 * DTO representing a successfully validated uploaded file (no error code).
 */
readonly class UploadedFileDTO
{
    /**
     * @param string $name The original name of the uploaded file.
     * @param string $type The MIME type of the uploaded file.
     * @param string $tmpPath The temporary server path where the file is stored.
     * @param int $size The size of the uploaded file in bytes.
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $tmpPath,
        public int $size,
    ) {
    }
}
