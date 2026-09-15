<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Providers\FileProvider;
use Ugarit\Ai\Responses\FileResponse;
use Ugarit\Ai\Responses\StoredFileResponse;

interface FileGateway
{
    /**
     * Get a file by its ID.
     */
    public function getFile(
        FileProvider $provider,
        string $fileId,
    ): FileResponse;

    /**
     * Store the given file.
     */
    public function putFile(
        FileProvider $provider,
        StorableFile $file,
    ): StoredFileResponse;

    /**
     * Delete a file by its ID.
     */
    public function deleteFile(
        FileProvider $provider,
        string $fileId,
    ): void;
}
