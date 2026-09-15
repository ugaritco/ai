<?php

namespace Ugarit\Ai\Gateway\Anthropic;

use Heritage\Http\Client\PendingRequest;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Gateway\FileGateway;
use Ugarit\Ai\Contracts\Providers\FileProvider;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\PreparesStorableFiles;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\FileResponse;
use Ugarit\Ai\Responses\StoredFileResponse;

class AnthropicFileGateway implements FileGateway
{
    use Concerns\CreatesAnthropicClient;
    use HandlesFailoverErrors;
    use PreparesStorableFiles;

    /**
     * Get a file by its ID.
     */
    public function getFile(FileProvider $provider, string $fileId): FileResponse
    {
        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)->get("files/{$fileId}"),
        );

        return new FileResponse(
            id: $response->json('id'),
            mimeType: $response->json('mime_type'),
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

        $providerOptions = $this->resolveProviderOptions($file, Lab::Anthropic);

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)
                ->attach('file', $content, $name, ['Content-Type' => $mime])
                ->post('files', $providerOptions),
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
            fn () => $this->client($provider)->delete("files/{$fileId}"),
        );
    }

    /**
     * Get an HTTP client for the Anthropic Files API.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        $config = $provider->additionalConfiguration();

        return $this->createClient(
            $this->baseUrl($provider),
            array_filter([
                'x-api-key' => $provider->providerCredentials()['key'],
                'anthropic-version' => $config['version'] ?? '2023-06-01',
                'anthropic-beta' => 'files-api-2025-04-14',
            ]),
            $config['headers'] ?? [],
            $timeout ?? 60,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function overloadedStatusCodes(): array
    {
        // 529 is Anthropic's own "overloaded" status, plus the shared transient gateway and Cloudflare codes.
        return [529, 502, 503, 504, 520, 522, 524];
    }
}
