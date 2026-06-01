<?php

namespace Goralys\Tests\Fakes;

use Goralys\App\HTTP\Files\Data\FileDTO;
use Goralys\App\HTTP\Files\Interface\FileMover;
use Goralys\Shared\Exception\GoralysRuntimeException;

class TestFileMover implements FileMover
{
    /** @var FileDTO[] */
    public array $files;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * @throws GoralysRuntimeException
     */
    public function move(string $from, string $destination): bool
    {
        if (!is_file($from)) {
            throw new GoralysRuntimeException("Source file does not exist: " . $from);
        }

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            throw new GoralysRuntimeException("Trying to move file to invalid directory: " . $dir);
        }

        if (!rename($from, $destination)) {
            throw new GoralysRuntimeException(
                sprintf('Failed to move file from "%s" to "%s"', $from, $destination),
            );
        }

        return true;
    }

    public function getFiles(): array
    {
        return $this->files ?? [];
    }
}
