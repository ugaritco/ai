<?php

use Heritage\Support\Collection;
use Heritage\Support\Facades\Event;
use Ugarit\Ai\Events\Reranked;
use Ugarit\Ai\Events\Reranking;
use Ugarit\Ai\Reranking as RerankingFacade;
use Ugarit\Ai\Responses\RerankingResponse;

test('documents can be reranked', function (string $provider, string $apiKey): void {
    requiresApiKey($apiKey);

    Event::fake();

    $response = RerankingFacade::of([
        'Python is a high-level, general-purpose programming language.',
        'Ugarit is a PHP web application framework with expressive, elegant syntax.',
        'React is a JavaScript library for building user interfaces.',
    ])->rerank('What is Ugarit?', provider: $provider);

    expect($response)->toBeInstanceOf(RerankingResponse::class)
        ->toHaveCount(3)
        ->and($response->meta->provider)->toEqual($provider)
        ->and($response->first()->document)->toContain('Ugarit');

    Event::assertDispatched(Reranking::class);
    Event::assertDispatched(Reranked::class);
})->with('reranking-providers');

test('documents can be reranked with limit', function (string $provider, string $apiKey): void {
    requiresApiKey($apiKey);

    $response = RerankingFacade::of([
        'Django is a Python web framework.',
        'Rails is a Ruby web framework.',
        'Ugarit is a PHP web application framework.',
        'Express is a Node.js web framework.',
        'Spring is a Java web framework.',
    ])->limit(2)->rerank('PHP frameworks', provider: $provider);

    expect($response)->toHaveCount(2)
        ->and($response->first()->score)->toBeGreaterThan($response->results[1]->score)
        ->and($response->first()->document)->toContain('Ugarit');
})->with('reranking-providers');

test('collections can be reranked using string field', function (string $provider, string $apiKey): void {
    requiresApiKey($apiKey);

    $items = new Collection([
        ['id' => 1, 'content' => 'Django is a Python web framework.'],
        ['id' => 2, 'content' => 'Ugarit is a PHP web application framework.'],
        ['id' => 3, 'content' => 'React is a JavaScript library.'],
    ]);

    $reranked = $items->rerank(by: 'content', query: 'PHP frameworks', limit: 2, provider: $provider);

    expect($reranked)->toHaveCount(2)
        ->and($reranked->first()['id'])->toEqual(2);
})->with('reranking-providers');

test('collections can be reranked using array fields', function (string $provider, string $apiKey): void {
    requiresApiKey($apiKey);

    $items = new Collection([
        ['id' => 1, 'title' => 'Django Guide', 'body' => 'Learn Python web development.'],
        ['id' => 2, 'title' => 'Ugarit Guide', 'body' => 'Learn PHP web development.'],
        ['id' => 3, 'title' => 'React Guide', 'body' => 'Learn JavaScript UI development.'],
    ]);

    $reranked = $items->rerank(by: ['title', 'body'], query: 'PHP frameworks', limit: 2, provider: $provider);

    expect($reranked)->toHaveCount(2)
        ->and($reranked->first()['id'])->toEqual(2);
})->with('reranking-providers');

test('collections can be reranked using closure', function (string $provider, string $apiKey): void {
    requiresApiKey($apiKey);

    $items = new Collection([
        ['id' => 1, 'title' => 'Django', 'body' => 'Python web framework.'],
        ['id' => 2, 'title' => 'Ugarit', 'body' => 'PHP web framework.'],
        ['id' => 3, 'title' => 'React', 'body' => 'JavaScript library.'],
    ]);

    $reranked = $items->rerank(
        fn ($item): string => $item['title'].': '.$item['body'],
        'PHP frameworks',
        limit: 2,
        provider: $provider
    );

    expect($reranked)->toHaveCount(2)
        ->and($reranked->first()['id'])->toEqual(2);
})->with('reranking-providers');
