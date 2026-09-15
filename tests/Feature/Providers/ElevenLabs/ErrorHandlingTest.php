<?php

use Heritage\Http\Client\RequestException;
use Heritage\Support\Facades\Http;
use Ugarit\Ai\Audio;
use Ugarit\Ai\Exceptions\ProviderOverloadedException;
use Ugarit\Ai\Exceptions\RateLimitedException;
use Ugarit\Ai\Transcription;

beforeEach(function (): void {
    config(['ai.providers.eleven' => [
        ...config('ai.providers.eleven'),
        'key' => 'test-key',
    ]]);
});

test('audio rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Rate limit exceeded'], 429),
    ]);

    Audio::of('Hello world')->generate(provider: 'eleven', model: 'eleven_multilingual_v2');
})->throws(RateLimitedException::class);

test('audio overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Service overloaded'], 503),
    ]);

    Audio::of('Hello world')->generate(provider: 'eleven', model: 'eleven_multilingual_v2');
})->throws(ProviderOverloadedException::class);

test('audio http error response throws request exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Unauthorized'], 401),
    ]);

    Audio::of('Hello world')->generate(provider: 'eleven', model: 'eleven_multilingual_v2');
})->throws(RequestException::class);

test('transcription rate limit response throws rate limited exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Rate limit exceeded'], 429),
    ]);

    Transcription::fromBase64(base64_encode('fake-audio'), 'audio/mp3')->generate(provider: 'eleven');
})->throws(RateLimitedException::class);

test('transcription overloaded response throws provider overloaded exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Service overloaded'], 503),
    ]);

    Transcription::fromBase64(base64_encode('fake-audio'), 'audio/mp3')->generate(provider: 'eleven');
})->throws(ProviderOverloadedException::class);

test('transcription http error response throws request exception', function (): void {
    Http::fake([
        'api.elevenlabs.io/*' => Http::response(['detail' => 'Unauthorized'], 401),
    ]);

    Transcription::fromBase64(base64_encode('fake-audio'), 'audio/mp3')->generate(provider: 'eleven');
})->throws(RequestException::class);
