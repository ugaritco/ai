<?php

use Heritage\Http\Client\RequestException;
use Heritage\Support\Facades\Http;
use Ugarit\Ai\Embeddings;
use Ugarit\Ai\Exceptions\ProviderOverloadedException;
use Ugarit\Ai\Exceptions\RateLimitedException;
use Ugarit\Ai\Reranking;

beforeEach(function (): void {
    config(['ai.providers.cohere' => [
        ...config('ai.providers.cohere'),
        'key' => 'test-key',
    ]]);
});

test('embeddings rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Rate limit exceeded'], 429),
    ]);

    Embeddings::for(['Hello'])->generate(provider: 'cohere', model: 'embed-v4.0');
})->throws(RateLimitedException::class);

test('embeddings overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Service overloaded'], 503),
    ]);

    Embeddings::for(['Hello'])->generate(provider: 'cohere', model: 'embed-v4.0');
})->throws(ProviderOverloadedException::class);

test('embeddings http error response throws request exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Unauthorized'], 401),
    ]);

    Embeddings::for(['Hello'])->generate(provider: 'cohere', model: 'embed-v4.0');
})->throws(RequestException::class);

test('reranking rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Rate limit exceeded'], 429),
    ]);

    Reranking::of(['doc1'])->rerank('What is AI?', provider: 'cohere', model: 'rerank-v3.5');
})->throws(RateLimitedException::class);

test('reranking overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Service overloaded'], 503),
    ]);

    Reranking::of(['doc1'])->rerank('What is AI?', provider: 'cohere', model: 'rerank-v3.5');
})->throws(ProviderOverloadedException::class);

test('reranking http error response throws request exception', function (): void {
    Http::fake([
        'api.cohere.com/*' => Http::response(['message' => 'Unauthorized'], 401),
    ]);

    Reranking::of(['doc1'])->rerank('What is AI?', provider: 'cohere', model: 'rerank-v3.5');
})->throws(RequestException::class);
