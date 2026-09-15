<?php

use Heritage\Http\Client\RequestException;
use Heritage\Support\Facades\Http;
use Ugarit\Ai\Exceptions\AiException;
use Ugarit\Ai\Exceptions\InsufficientCreditsException;
use Ugarit\Ai\Exceptions\ProviderOverloadedException;
use Ugarit\Ai\Exceptions\RateLimitedException;
use Tests\Fixtures\Agents\AssistantAgent;

beforeEach(function (): void {
    config(['ai.providers.openrouter' => [
        ...config('ai.providers.openrouter'),
        'key' => 'test-key',
    ]]);
});

test('http error response throws request exception', function (): void {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => ['message' => 'Bad request']], 400)]);

    (new AssistantAgent)->prompt('Hello', provider: 'openrouter');
})->throws(RequestException::class);

test('rate limit response throws rate limited exception', function (): void {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => ['message' => 'Rate limited']], 429)]);

    (new AssistantAgent)->prompt('Hello', provider: 'openrouter');
})->throws(RateLimitedException::class);

test('overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'openrouter.ai/*' => Http::response([
            'error' => [
                'type' => 'server_error',
                'message' => 'The server is currently overloaded. Please try again later.',
            ],
        ], 503),
    ]);

    (new AssistantAgent)->prompt('Hello', provider: 'openrouter');
})->throws(ProviderOverloadedException::class);

test('402 response throws insufficient credits exception', function (): void {
    Http::fake([
        'openrouter.ai/*' => Http::response([
            'error' => [
                'code' => 402,
                'message' => 'Insufficient credits. Add more credits at https://openrouter.ai/credits',
            ],
        ], 402),
    ]);

    (new AssistantAgent)->prompt('Hello', provider: 'openrouter');
})->throws(InsufficientCreditsException::class);

test('error in 200 response throws ai exception', function (): void {
    Http::fake(['openrouter.ai/*' => Http::response([
        'error' => [
            'type' => 'invalid_request_error',
            'message' => 'The model does not exist.',
        ],
    ])]);

    (new AssistantAgent)->prompt('Hello', provider: 'openrouter');
})->throws(AiException::class, 'OpenRouter Error');
