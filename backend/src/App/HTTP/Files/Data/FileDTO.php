<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Files\Data;

use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * Raw DTO representing a single file entry from a PHP file upload (mirrors `$_FILES` structure).
 */
readonly class FileDTO
{
    /**
     * @param string $name The original name of the file.
     * @param string $type The MIME type of the file.
     * @param string $tmpPath The temporary server path where the file is stored.
     * @param int $size The size of the file in bytes.
     * @param int $error The PHP upload error code (one of the UPLOAD_ERR_* constants).
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $tmpPath,
        public int $size,
        public int $error,
    ) {
    }

    /**
     * Validates a FileDTO object and turns it into an UploadedFileDTO if the validation pass.
     * @throws GoralysRuntimeException If the validation fails.
     */
    public function toUploadedFile(): UploadedFileDTO
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new GoralysRuntimeException("Failed to validate invalid file : " . $this->name);
        }

        return new UploadedFileDTO(
            $this->name,
            $this->type,
            $this->tmpPath,
            $this->size,
        );
    }
}
