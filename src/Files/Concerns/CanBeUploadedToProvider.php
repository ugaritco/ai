<?php

namespace Ugarit\Ai\Files\Concerns;

use Ugarit\Ai\Files;
use Ugarit\Ai\Responses\StoredFileResponse;

trait CanBeUploadedToProvider
{
    /**
     * Store the file on a given provider.
     */
    public function put(?string $mimeType = null, ?string $name = null, ?string $provider = null): StoredFileResponse
    {
        return Files::put(
            $this,
            mimeType: $mimeType ?? $this->mimeType() ?? null,
            name: $name ?? $this->name() ?? null,
            provider: $provider
        );
    }
}
