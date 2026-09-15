<?php

namespace Ugarit\Ai\Tools;

use Heritage\Contracts\Filesystem\Filesystem;
use Heritage\Support\Collection;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Filesystem\CopyFile;
use Ugarit\Ai\Tools\Filesystem\DeleteFile;
use Ugarit\Ai\Tools\Filesystem\FileExists;
use Ugarit\Ai\Tools\Filesystem\GetFileMetadata;
use Ugarit\Ai\Tools\Filesystem\GetFileUrl;
use Ugarit\Ai\Tools\Filesystem\ListFiles;
use Ugarit\Ai\Tools\Filesystem\ReadFile;
use Ugarit\Ai\Tools\Filesystem\WriteFile;

class FileStorage
{
    /**
     * Create all of the file storage tools for the given disk.
     *
     * @return Collection<int, Tool>
     */
    public static function all(string|Filesystem|null $disk = null): Collection
    {
        return static::readOnly($disk)->merge([
            new WriteFile($disk),
            new DeleteFile($disk),
            new CopyFile($disk),
        ]);
    }

    /**
     * Create the read-only file storage tools for the given disk.
     *
     * @return Collection<int, Tool>
     */
    public static function readOnly(string|Filesystem|null $disk = null): Collection
    {
        return new Collection([
            new ListFiles($disk),
            new ReadFile($disk),
            new FileExists($disk),
            new GetFileMetadata($disk),
            new GetFileUrl($disk),
        ]);
    }
}
