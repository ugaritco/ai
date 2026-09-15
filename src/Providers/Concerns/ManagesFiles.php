<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Events\FileDeleted;
use Ugarit\Ai\Events\FileStored;
use Ugarit\Ai\Events\StoringFile;
use Ugarit\Ai\Responses\FileResponse;
use Ugarit\Ai\Responses\StoredFileResponse;

trait ManagesFiles
{
    /**
     * Get a file by its ID.
     */
    public function getFile(string $fileId): FileResponse
    {
        return $this->fileGateway()->getFile($this, $fileId);
    }

    /**
     * Store the given file.
     */
    public function putFile(StorableFile $file): StoredFileResponse
    {
        $invocationId = (string) Str::uuid7();

        if (Ai::filesAreFaked()) {
            Ai::recordFileUpload($file);
        }

        $this->events->dispatch(new StoringFile(
            $invocationId, $this, $file
        ));

        return tap(
            $this->fileGateway()->putFile($this, $file),
            function (StoredFileResponse $response) use ($invocationId, $file): void {
                $this->events->dispatch(new FileStored(
                    $invocationId, $this, $file, $response,
                ));
            }
        );
    }

    /**
     * Delete a file by its ID.
     */
    public function deleteFile(string $fileId): void
    {
        $invocationId = (string) Str::uuid7();

        if (Ai::filesAreFaked()) {
            Ai::recordFileDeletion($fileId);
        }

        $this->fileGateway()->deleteFile($this, $fileId);

        $this->events->dispatch(new FileDeleted(
            $invocationId, $this, $fileId,
        ));
    }
}
