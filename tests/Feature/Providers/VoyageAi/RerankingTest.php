<?php

use Heritage\Http\Client\Request;
use Heritage\Http\Client\RequestException;
use Heritage\Support\Facades\Http;
use Ugarit\Ai\Exceptions\ProviderOverloadedException;
use Ugarit\Ai\Exceptions\RateLimitedException;
use Ugarit\Ai\Reranking;
use Ugarit\Ai\Responses\Data\RankedDocument;

beforeEach(function (): void {
    config(['ai.providers.voyageai' => [
        ...config('ai.providers.voyageai'),
        'key' => 'test-key',
    ]]);
});

test('reranking request includes model, query, and documents', function (): void {
    Http::fake(['*' => fakeVoyageRerankingResponse()]);

    Reranking::of(['Ugarit is a PHP framework', 'React is a JS library'])
        ->rerank('What is Ugarit?', provider: 'voyageai', model: 'rerank-2.5-lite');

    Http::assertSent(function (Request $request): bool {
        $body = json_decode($request->body(), true);

        return $body['model'] === 'rerank-2.5-lite'
            && $body['query'] === 'What is Ugarit?'
            && $body['documents'] === ['Ugarit is a PHP framework', 'React is a JS library']
            && ! array_key_exists('top_k', $body)
            && $request->url() === 'https://api.voyageai.com/v1/rerank';
    });
});

test('reranking request includes top_k when limit set', function (): void {
    Http::fake(['*' => fakeVoyageRerankingResponse()]);

    Reranking::of(['Doc A', 'Doc B', 'Doc C'])
        ->limit(2)
        ->rerank('query', provider: 'voyageai', model: 'rerank-2.5-lite');

    Http::assertSent(fn (Request $request): bool => json_decode($request->body(), true)['top_k'] === 2);
});

test('reranking response is correctly parsed into RankedDocuments', function (): void {
    Http::fake(['*' => fakeVoyageRerankingResponse()]);

    $response = Reranking::of(['Ugarit is a PHP framework', 'React is a JS library'])
        ->rerank('What is Ugarit?', provider: 'voyageai', model: 'rerank-2.5-lite');

    expect($response)->toHaveCount(2)
        ->and($response->first())->toBeInstanceOf(RankedDocument::class)
        ->and($response->first()->index)->toBe(0)
        ->and($response->first()->document)->toBe('Ugarit is a PHP framework')
        ->and($response->first()->score)->toBe(0.95)
        ->and($response->meta->provider)->toBe('voyageai')
        ->and($response->meta->model)->toBe('rerank-2.5-lite');
});

test('reranking request sends bearer token', function (): void {
    Http::fake(['*' => fakeVoyageRerankingResponse()]);

    Reranking::of(['Doc A', 'Doc B'])->rerank('query', provider: 'voyageai', model: 'rerank-2.5-lite');

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer test-key'));
});

test('reranking rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.voyageai.com/*' => Http::response(['detail' => 'Rate limit exceeded'], 429),
    ]);

    Reranking::of(['Doc A', 'Doc B'])->rerank('query', provider: 'voyageai', model: 'rerank-2.5-lite');
})->throws(RateLimitedException::class);

test('reranking overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.voyageai.com/*' => Http::response(['detail' => 'Service unavailable'], 503),
    ]);

    Reranking::of(['Doc A', 'Doc B'])->rerank('query', provider: 'voyageai', model: 'rerank-2.5-lite');
})->throws(ProviderOverloadedException::class);

test('reranking http error response throws request exception', function (): void {
    Http::fake([
        'api.voyageai.com/*' => Http::response(['detail' => 'Invalid model'], 400),
    ]);

    Reranking::of(['Doc A', 'Doc B'])->rerank('query', provider: 'voyageai', model: 'rerank-2.5-lite');
})->throws(RequestException::class);

function fakeVoyageRerankingResponse()
{
    return Http::response([
        'object' => 'list',
        'data' => [
            ['index' => 0, 'relevance_score' => 0.95],
            ['index' => 1, 'relevance_score' => 0.12],
        ],
        'model' => 'rerank-2.5-lite',
        'usage' => ['total_tokens' => 25],
    ]);
}
