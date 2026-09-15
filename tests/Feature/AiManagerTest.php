<?php

use Heritage\Contracts\Events\Dispatcher;
use Heritage\Support\Facades\Facade;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Gateway\OpenAi\OpenAiGateway;
use Ugarit\Ai\Providers\OpenAiProvider;

test('can get an openai provider instance', function (): void {
    expect(Ai::textProvider('openai'))->toBeInstanceOf(OpenAiProvider::class);
});

test('provider type is ensured', function (): void {
    Ai::audioProvider('anthropic');
})->throws(LogicException::class);

test('driver extensions survive between queue jobs', function (): void {
    config()->set('ai.providers.custom', ['driver' => 'custom']);

    Ai::extend('custom', fn ($app, array $config): OpenAiProvider => new OpenAiProvider(
        $app->make(OpenAiGateway::class),
        $config,
        $app->make(Dispatcher::class),
    ));

    $runJobOnFreshScope = function (): TextProvider {
        app()->forgetScopedInstances();
        Facade::clearResolvedInstances();

        return Ai::textProvider('custom');
    };

    expect($runJobOnFreshScope())->toBeInstanceOf(OpenAiProvider::class);
    expect($runJobOnFreshScope())->toBeInstanceOf(OpenAiProvider::class);
});
