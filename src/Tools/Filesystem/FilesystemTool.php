<?php

namespace Ugarit\Ai\Tools\Filesystem;

use Heritage\Contracts\Filesystem\Filesystem;
use Heritage\Support\Facades\Storage;
use Ugarit\Ai\Contracts\Tool;
use Throwable;

abstract class FilesystemTool implements Tool
{
    public function __construct(protected Filesystem|string|null $disk = null) {}

    /**
     * Determine whether the given path points to a file, not a directory.
     */
    protected function fileExists(Filesystem $disk, string $path): bool
    {
        if (method_exists($disk, 'fileExists')) {
            return $disk->fileExists($path);
        }

        try {
            $disk->size($path);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Resolve the filesystem disk the tool operates on.
     */
    protected function disk(): Filesystem
    {
        return $this->disk instanceof Filesystem
            ? $this->disk
            : Storage::disk($this->disk);
    }
}
