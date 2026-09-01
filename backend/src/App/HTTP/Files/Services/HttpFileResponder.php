<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\App\HTTP\Files\Services;

use Goralys\App\HTTP\Files\Interface\FileResponder;
use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\Files\InvalidFileException;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * The HTTP service used to send files to the frontend.
 */
class HttpFileResponder implements FileResponder
{
    /**
     * Sends a given file to the frontend.
     * @param string $path The path of the file to send.
     * @param string $name The name of the file when downloaded by the clien.
     * @throws GoralysRuntimeException|InvalidFileException If the file cannot be sent to the frontend.
     */
    public function send(string $path, string $name): void
    {
        $realpath = realpath($path);
        if (!$realpath || !is_file($realpath)) {
            throw new InvalidFileException("$path is not a valid file and could not be sent");
        }

        $baseDir = realpath(Config::DIRECTORIES::ASSETS);
        $tmpDir = realpath(sys_get_temp_dir());
        if ($baseDir === false) {
            throw new GoralysRuntimeException("Base directory could not be resolved");
        }

        if (
            !str_starts_with($realpath, $baseDir . DIRECTORY_SEPARATOR)
            && !((pathinfo($path, PATHINFO_EXTENSION) === "tmp")
                  || str_starts_with($path, $tmpDir . DIRECTORY_SEPARATOR))
        ) {
            throw new GoralysRuntimeException("Unauthorized file access");
        }

        if (headers_sent($file, $line)) {
            throw new GoralysRuntimeException("Headers were already sent at $file:$line");
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . mime_content_type($realpath) ?: "application/octet-stream");
        header('Content-Disposition: attachment; filename="' . basename($name) . '"');
        header('Content-Length: ' . filesize($realpath));
        header('X-Content-Type-Options: nosniff');

        readfile($realpath);
    }
}
