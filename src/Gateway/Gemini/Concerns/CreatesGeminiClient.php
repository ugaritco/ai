<?php

namespace Ugarit\Ai\Gateway\Gemini\Concerns;

use Heritage\Http\Client\PendingRequest;
use Ugarit\Ai\Gateway\Concerns\CreatesClient;
use Ugarit\Ai\Providers\Provider;

trait CreatesGeminiClient
{
    use CreatesClient;

    /**
     * Get an HTTP client for the Gemini API.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        return $this->createClient(
            $this->baseUrl($provider),
            array_filter(['x-goog-api-key' => $provider->providerCredentials()['key']]),
            $provider->additionalConfiguration()['headers'] ?? [],
            $timeout ?? 60,
        );
    }

    /**
     * Get the base URL for the Gemini API.
     */
    protected function baseUrl(Provider $provider): string
    {
        return rtrim($provider->additionalConfiguration()['url'] ?? 'https://generativelanguage.googleapis.com/v1beta/', '/');
    }
}
