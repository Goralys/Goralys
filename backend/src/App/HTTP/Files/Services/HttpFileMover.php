<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Files\Services;

use Goralys\App\HTTP\Files\Data\FileDTO;
use Goralys\App\HTTP\Files\Interface\FileMover;
use Goralys\App\HTTP\Files\Utils\FilesNormalizer;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * The HTTP service used to move files inside the backend.
 */
final class HttpFileMover implements FileMover
{
    /** @var ?FileDTO[]  */
    public ?array $files = null;

    public function getFiles(): array
    {
        if (!$this->files) {
            $this->files = FilesNormalizer::fromGlobals($_FILES);
        }
        return $this->files;
    }

    /**
     * Moves a given file.
     * @param string $from The original path of the file.
     * @param string $destination The destination of the file.
     * @return bool If the move was successful or not.
     * @throws GoralysRuntimeException If the file is invalid.
     */
    public function move(string $from, string $destination): bool
    {
        if (!is_uploaded_file($from)) {
            throw new GoralysRuntimeException("Invalid uploaded file: " . $from);
        }

        if (!move_uploaded_file($from, $destination)) {
            throw new GoralysRuntimeException(
                sprintf('Failed to move uploaded file from "%s" to "%s"', $from, $destination),
            );
        }

        return true;
    }
}
