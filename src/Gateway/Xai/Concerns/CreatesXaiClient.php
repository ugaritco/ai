<?php

namespace Ugarit\Ai\Gateway\Xai\Concerns;

use Heritage\Http\Client\PendingRequest;
use Ugarit\Ai\Gateway\Concerns\CreatesClient;
use Ugarit\Ai\Providers\Provider;

trait CreatesXaiClient
{
    use CreatesClient;

    /**
     * Get an HTTP client for the xAI API.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        return $this->createClient(
            $this->baseUrl($provider),
            ['Authorization' => 'Bearer '.$provider->providerCredentials()['key']],
            $provider->additionalConfiguration()['headers'] ?? [],
            $timeout ?? 60,
        );
    }

    /**
     * Get the base URL for the xAI API.
     */
    protected function baseUrl(Provider $provider): string
    {
        return rtrim($provider->additionalConfiguration()['url'] ?? 'https://api.x.ai/v1', '/');
    }
}
