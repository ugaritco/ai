<?php

namespace Ugarit\Ai\Gateway\Groq;

use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Contracts\Gateway\TranscriptionGateway;
use Ugarit\Ai\Contracts\Providers\TranscriptionProvider;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\ParsesServerSentEvents;
use Ugarit\Ai\Gateway\Concerns\ResolvesAudioFilenames;
use Ugarit\Ai\Gateway\OpenAiCompatible\Concerns\MapsChatCompletionMessages;
use Ugarit\Ai\Gateway\OpenAiCompatible\Concerns\MapsChatCompletionTools;
use Ugarit\Ai\Gateway\OpenAiCompatible\Concerns\PerformsChatCompletionSteps;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\TranscriptionSegment;
use Ugarit\Ai\Responses\Data\Usage;
use Ugarit\Ai\Responses\TranscriptionResponse;
use LogicException;

class GroqGateway implements StepTextGateway, TranscriptionGateway
{
    use Concerns\BuildsTextRequests;
    use Concerns\CreatesGroqClient;
    use Concerns\HandlesTextStreaming;
    use Concerns\MapsAttachments;
    use Concerns\ParsesTextResponses;
    use HandlesFailoverErrors;
    use MapsChatCompletionMessages;
    use MapsChatCompletionTools;
    use ParsesServerSentEvents;
    use PerformsChatCompletionSteps;
    use ResolvesAudioFilenames;

    public function __construct(protected Dispatcher $events) {}

    /**
     * The status codes that indicate Groq is transiently unavailable and the request should fail over.
     *
     * @return list<int>
     */
    protected function overloadedStatusCodes(): array
    {
        // The status codes Groq documents as transient: 498 is "flex tier capacity exceeded", alongside 502 and 503.
        return [498, 502, 503];
    }

    /**
     * Generate text from the given audio.
     *
     * @param  array<string, mixed>  $providerOptions
     */
    public function generateTranscription(
        TranscriptionProvider $provider,
        string $model,
        TranscribableAudio $audio,
        ?string $language = null,
        bool $diarize = false,
        int $timeout = 30,
        array $providerOptions = [],
    ): TranscriptionResponse {
        if ($diarize) {
            throw new LogicException(
                'Groq does not support diarized transcription. Use the OpenAI, ElevenLabs, Mistral, or Gemini provider for diarization.'
            );
        }

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)
                ->attach('file', $audio->content(), $this->audioFilename($audio), array_filter(['Content-Type' => $audio->mimeType()]))
                ->post('audio/transcriptions', array_merge($providerOptions, array_filter([
                    'model' => $model,
                    'language' => $language,
                    'response_format' => $providerOptions['response_format'] ?? 'json',
                ]))),
        );

        $data = $response->json();

        return new TranscriptionResponse(
            $data['text'] ?? '',
            collect($data['segments'] ?? [])->map(fn (array $segment): TranscriptionSegment => new TranscriptionSegment(
                $segment['text'] ?? '',
                $segment['speaker'] ?? '',
                $segment['start'] ?? 0,
                $segment['end'] ?? 0,
            )),
            new Usage(
                $data['usage']['prompt_tokens'] ?? 0,
                $data['usage']['completion_tokens'] ?? 0,
            ),
            new Meta($provider->name(), $model),
        );
    }
}
