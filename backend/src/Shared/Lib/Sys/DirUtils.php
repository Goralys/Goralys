<?php

namespace Goralys\Shared\Lib\Sys;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final readonly class DirUtils
{
    /**
     * Removes a directory and all of its content recursively.
     * This implementation explicitly avoid recursion because PHP does not support tail call optimization, thus a big
     * recursive call would blow up the stack very quickly.
     * @param string $dir The Path to the directory that should be removed.
     * @return ?list<string> The list of all the paths that could not be removed, null if all paths were removed
     * succesfully.
     */
    public static function rmDirRecursive(string $dir): ?array
    {
        $failed = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($it as $file) {
            $path = $file->getPathname();
            if ($file->isDir()) {
                if (!rmdir($path)) {
                    $failed[] = $path;
                }
            } else {
                if (!unlink($path)) {
                    $failed[] = $path;
                }
            }
        }

        if (!rmdir($dir)) {
            $failed[] = $dir;
        }

        return empty($failed) ? null : $failed;
    }
}
