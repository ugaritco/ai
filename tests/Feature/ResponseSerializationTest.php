<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Heritage\Http\Client\Response;
use Ugarit\Ai\Responses\Data\FinishReason;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\Usage;
use Tests\Fixtures\Responses\SubclassedStep;
use Tests\Fixtures\Responses\SubclassedTextResponse;

test('serialization preserves private properties declared on text response subclasses', function (): void {
    $response = new SubclassedTextResponse('Hello', new Usage(1, 2), new Meta('anthropic', 'claude'));
    $response->rememberSecret('changed');
    $response->withRawResponse(new Response(new Psr7Response(200, [], '{}')));

    $restored = unserialize(serialize($response));

    expect($restored->secret())->toBe('changed')
        ->and($restored->text)->toBe('Hello')
        ->and($restored->raw)->toBeNull();
});

test('serialization preserves private properties declared on step subclasses', function (): void {
    $step = new SubclassedStep('Hello', [], [], FinishReason::Stop, new Usage(1, 2), new Meta('anthropic', 'claude'));
    $step->rememberSecret('changed');
    $step->withRawResponse(new Response(new Psr7Response(200, [], '{}')));

    $restored = unserialize(serialize($step));

    expect($restored->secret())->toBe('changed')
        ->and($restored->text)->toBe('Hello')
        ->and($restored->raw)->toBeNull();
});
