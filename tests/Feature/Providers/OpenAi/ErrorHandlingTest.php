<?php

use Heritage\Http\Client\RequestException;
use Heritage\Support\Facades\Http;
use Ugarit\Ai\Exceptions\AiException;
use Ugarit\Ai\Exceptions\ProviderOverloadedException;
use Ugarit\Ai\Exceptions\RateLimitedException;
use Tests\Fixtures\Agents\AssistantAgent;

beforeEach(function (): void {
    config(['ai.providers.openai' => [
        ...config('ai.providers.openai'),
        'key' => 'test-key',
    ]]);
});

test('http error response throws request exception', function (): void {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Invalid request: max_output_tokens must be at least 1',
            ],
        ], 400),
    ]);

    (new AssistantAgent)->prompt(
        'Hi',
        provider: 'openai',
    );
})->throws(RequestException::class);

test('rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'error' => [
                'type' => 'rate_limit_error',
                'message' => 'Rate limit exceeded',
            ],
        ], 429),
    ]);

    (new AssistantAgent)->prompt(
        'Hi',
        provider: 'openai',
    );
})->throws(RateLimitedException::class);

test('overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'error' => [
                'type' => 'server_error',
                'message' => 'The server is currently overloaded. Please try again later.',
            ],
        ], 503),
    ]);

    (new AssistantAgent)->prompt(
        'Hi',
        provider: 'openai',
    );
})->throws(ProviderOverloadedException::class);

test('error in 200 response throws ai exception', function (): void {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'error' => [
                'type' => 'api_error',
                'message' => 'Internal server error',
            ],
        ], 200),
    ]);

    (new AssistantAgent)->prompt(
        'Hi',
        provider: 'openai',
    );
})->throws(AiException::class, 'OpenAI Error');

test('failed status response throws ai exception', function (): void {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'id' => 'resp_123',
            'status' => 'failed',
            'model' => 'gpt-5.4',
            'output' => [],
        ], 200),
    ]);

    (new AssistantAgent)->prompt(
        'Hi',
        provider: 'openai',
    );
})->throws(AiException::class, 'OpenAI Error');
