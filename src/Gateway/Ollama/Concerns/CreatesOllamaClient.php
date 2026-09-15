<?php

namespace Ugarit\Ai\Gateway\Ollama\Concerns;

use Heritage\Http\Client\PendingRequest;
use Ugarit\Ai\Gateway\Concerns\CreatesClient;
use Ugarit\Ai\Providers\Provider;

trait CreatesOllamaClient
{
    use CreatesClient;

    /**
     * Get an HTTP client for the Ollama API.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        $key = $provider->providerCredentials()['key'] ?? null;

        return $this->createClient(
            $this->baseUrl($provider),
            filled($key) ? ['Authorization' => 'Bearer '.$key] : [],
            $provider->additionalConfiguration()['headers'] ?? [],
            $timeout ?? 60,
        );
    }

    /**
     * Get the base URL for the Ollama API.
     */
    protected function baseUrl(Provider $provider): string
    {
        return rtrim($provider->additionalConfiguration()['url'] ?? 'http://localhost:11434', '/');
    }
}
