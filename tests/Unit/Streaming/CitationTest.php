<?php

use Ugarit\Ai\Responses\Data\UrlCitation;
use Ugarit\Ai\Streaming\Events\Citation;
use Ugarit\Ai\Streaming\Events\TextDelta;

function citation(string $url, ?string $title = null): Citation
{
    return new Citation(uniqid(), 'message-1', new UrlCitation($url, $title), time());
}

test('combine collects the sources a run cited', function () {
    $citations = Citation::combine([
        citation('https://ugarit.com/docs', 'Ugarit Documentation'),
        citation('https://ugarit.com/docs/mcp', 'Ugarit MCP'),
    ]);

    expect($citations)->toHaveCount(2)
        ->and($citations->first()->url)->toBe('https://ugarit.com/docs')
        ->and($citations->first()->title)->toBe('Ugarit Documentation');
});

test('combine keeps every mention, the way a generated response does', function () {
    // The generated path records every mention, so the streamed path does too...
    $citations = Citation::combine([
        citation('https://ugarit.com/docs', 'Ugarit Documentation'),
        citation('https://ugarit.com/docs/mcp', 'Ugarit MCP'),
        citation('https://ugarit.com/docs', 'Ugarit Documentation'),
    ]);

    expect($citations)->toHaveCount(3)
        ->and($citations->pluck('url')->all())->toBe([
            'https://ugarit.com/docs',
            'https://ugarit.com/docs/mcp',
            'https://ugarit.com/docs',
        ]);
});

test('combine ignores events that are not citations', function () {
    $citations = Citation::combine([
        new TextDelta(uniqid(), 'message-1', 'Ugarit MCP ships a server.', time()),
        citation('https://ugarit.com/docs/mcp'),
    ]);

    expect($citations)->toHaveCount(1);
});

test('combine returns nothing when the run cited nothing', function () {
    expect(Citation::combine([
        new TextDelta(uniqid(), 'message-1', 'It is 12°C.', time()),
    ]))->toBeEmpty();
});
