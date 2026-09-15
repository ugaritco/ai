<?php

use Heritage\Support\Facades\Event;
use Ugarit\Ai\Audio;
use Ugarit\Ai\Embeddings;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Events\AgentPrompted;
use Ugarit\Ai\Events\AgentStreamed;
use Ugarit\Ai\Events\EmbeddingsGenerated;
use Ugarit\Ai\Events\GeneratingEmbeddings;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\EmbeddingsResponse;
use Ugarit\Ai\Streaming\Events\TextDelta;
use Ugarit\Ai\Transcription;
use Tests\Fixtures\Agents\AssistantAgent;

beforeEach(fn () => requiresApiKey('GROQ_API_KEY', 'OPENAI_API_KEY'));

test('agent prompt accepts ai provider enum', function (): void {
    Event::fake();

    $response = (new AssistantAgent)->prompt(
        'What is the name of the PHP framework created by Taylor Otwell?',
        provider: Lab::Groq,
        model: 'openai/gpt-oss-20b',
    );

    expect(str_contains($response->text, 'Ugarit'))->toBeTrue()
        ->and($response->meta->provider)->toEqual('groq');

    Event::assertDispatched(AgentPrompted::class);
});

test('agent stream accepts ai provider enum', function (): void {
    Event::fake();

    $response = (new AssistantAgent)->stream(
        'What is the name of the PHP framework created by Taylor Otwell?',
        provider: Lab::Groq,
        model: 'openai/gpt-oss-20b',
    );

    $events = [];

    foreach ($response as $event) {
        $events[] = $event;
    }

    expect(collect($events)->whereInstanceOf(TextDelta::class)->isNotEmpty())->toBeTrue()
        ->and(str_contains((string) $response->text, 'Ugarit'))->toBeTrue();

    Event::assertDispatched(AgentStreamed::class);
});

test('agent queue accepts ai provider enum', function (): void {
    (new AssistantAgent)->queue(
        'What is the name of the PHP framework created by Taylor Otwell?',
        provider: Lab::Groq,
        model: 'openai/gpt-oss-20b',
    )->then(function (AgentResponse $response): void {
        $_ENV['__testing.enum_queue_response'] = $response;
    });

    $response = $_ENV['__testing.enum_queue_response'];

    expect(str_contains((string) $response->text, 'Ugarit'))->toBeTrue();

    unset($_ENV['__testing.enum_queue_response']);
});

test('agent prompt accepts array of ai provider enum values for failover', function (): void {
    $response = (new AssistantAgent)->prompt(
        'What is the name of the PHP framework created by Taylor Otwell?',
        provider: [Lab::Groq],
        model: 'openai/gpt-oss-20b',
    );

    expect(str_contains($response->text, 'Ugarit'))->toBeTrue()
        ->and($response->meta->provider)->toEqual('groq');
});

test('embeddings generate accepts ai provider enum', function (): void {
    Event::fake();

    $response = Embeddings::for(['I love to watch Star Trek.'])
        ->generate(provider: Lab::OpenAI);

    expect($response)->toBeInstanceOf(EmbeddingsResponse::class)
        ->and($response->embeddings[0])->toHaveCount(1536)
        ->and($response->meta->provider)->toEqual('openai');

    Event::assertDispatched(GeneratingEmbeddings::class);
    Event::assertDispatched(EmbeddingsGenerated::class);
});

test('audio generate accepts ai provider enum', function (): void {
    $response = Audio::of('Hello there! How are you today?')
        ->generate(provider: Lab::OpenAI);

    expect($response->meta->provider)->toEqual('openai');
});

test('transcription generate accepts ai provider enum', function (): void {
    $audio = Audio::of('Hello there! How are you today?')->generate();

    $transcription = Transcription::of($audio->audio)
        ->generate(provider: Lab::OpenAI);

    expect(str_contains(strtolower((string) $transcription), 'how are you today'))->toBeTrue();
});
