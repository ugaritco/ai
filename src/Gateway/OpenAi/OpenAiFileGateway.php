<?php

namespace Ugarit\Ai\Gateway\OpenAi;

use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Gateway\FileGateway;
use Ugarit\Ai\Contracts\Providers\FileProvider;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\PreparesStorableFiles;
use Ugarit\Ai\Responses\FileResponse;
use Ugarit\Ai\Responses\StoredFileResponse;

class OpenAiFileGateway implements FileGateway
{
    use Concerns\CreatesOpenAiClient;
    use HandlesFailoverErrors;
    use PreparesStorableFiles;

    /**
     * Get a file by its ID.
     */
    public function getFile(FileProvider $provider, string $fileId): FileResponse
    {
        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)
                ->get("files/{$fileId}")
        );

        return new FileResponse(
            id: $response->json('id'),
        );
    }

    /**
     * Store the given file.
     */
    public function putFile(
        FileProvider $provider,
        StorableFile $file,
    ): StoredFileResponse {
        [$content, $mime, $name] = $this->prepareStorableFile($file);

        $providerOptions = $this->resolveProviderOptions($file, $this->providerOptionsKey());

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)
                ->attach('file', $content, $name, ['Content-Type' => $mime])
                ->post('files', array_merge(
                    ['purpose' => $this->defaultPurpose()],
                    $providerOptions,
                ))
        );

        return new StoredFileResponse($response->json('id'));
    }

    /**
     * Delete a file by its ID.
     */
    public function deleteFile(FileProvider $provider, string $fileId): void
    {
        $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)
                ->delete("files/{$fileId}")
        );
    }

    /**
     * Get the default purpose to use when a file does not specify one.
     */
    protected function defaultPurpose(): string
    {
        return 'user_data';
    }

    /**
     * Get the provider key used to resolve file upload options.
     */
    protected function providerOptionsKey(): Lab
    {
        return Lab::OpenAI;
    }
}
