<?php

namespace Ugarit\Ai\Gateway\Gemini;

use Heritage\Http\Client\PendingRequest;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Gateway\FileGateway;
use Ugarit\Ai\Contracts\Providers\FileProvider;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Gateway\Concerns\CreatesClient;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\PreparesStorableFiles;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\FileResponse;
use Ugarit\Ai\Responses\StoredFileResponse;

class GeminiFileGateway implements FileGateway
{
    use CreatesClient;
    use HandlesFailoverErrors;
    use PreparesStorableFiles;

    /**
     * Get a file by its ID.
     */
    public function getFile(FileProvider $provider, string $fileId): FileResponse
    {
        $fileId = str_starts_with($fileId, 'files/') ? $fileId : "files/{$fileId}";

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)->get($this->baseUrl($provider)."/{$fileId}")->throw(),
        );

        return new FileResponse(
            id: $response->json('name'),
            mimeType: $response->json('mimeType'),
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

        $uploadUrl = str_replace('/v1beta', '/upload/v1beta', $this->baseUrl($provider));

        $providerOptions = $this->resolveProviderOptions($file, Lab::Gemini);

        $response = $this->withErrorHandling($provider->name(), fn () => $this->client($provider)->attach(
            'file', $content, $name, ['Content-Type' => $mime]
        )->post("{$uploadUrl}/files", array_replace_recursive([
            'file' => ['display_name' => $name],
        ], $providerOptions))->throw());

        return new StoredFileResponse($response->json('file.name'));
    }

    /**
     * Delete a file by its ID.
     */
    public function deleteFile(FileProvider $provider, string $fileId): void
    {
        $fileId = str_starts_with($fileId, 'files/') ? $fileId : "files/{$fileId}";

        $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)->delete($this->baseUrl($provider)."/{$fileId}")->throw(),
        );
    }

    protected function client(Provider $provider): PendingRequest
    {
        return $this->createClient(
            $this->baseUrl($provider),
            array_filter(['x-goog-api-key' => $provider->providerCredentials()['key']]),
            $provider->additionalConfiguration()['headers'] ?? [],
            timeout: null,
            throw: false,
        );
    }

    /**
     * Get the base URL for the Gemini API.
     */
    protected function baseUrl(Provider $provider): string
    {
        return rtrim($provider->additionalConfiguration()['url'] ?? 'https://generativelanguage.googleapis.com/v1beta', '/');
    }
}
