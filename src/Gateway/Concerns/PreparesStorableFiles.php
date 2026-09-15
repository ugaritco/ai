<?php

namespace Ugarit\Ai\Gateway\Concerns;

use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Enums\Lab;

trait PreparesStorableFiles
{
    /**
     * Prepare file data for upload.
     *
     * @return array{string, string, string}
     */
    protected function prepareStorableFile(StorableFile $file): array
    {
        return [
            $file->content(),
            $file->mimeType() ?? 'application/octet-stream',
            $file->name() ?? 'file',
        ];
    }

    /**
     * Resolve the provider-specific upload options for the given file.
     *
     * @return array<string, mixed>
     */
    protected function resolveProviderOptions(StorableFile $file, Lab|string $provider): array
    {
        return $file instanceof HasProviderOptions ? $file->providerOptions($provider) : [];
    }
}
