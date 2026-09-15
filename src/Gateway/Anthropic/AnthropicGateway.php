<?php

namespace Ugarit\Ai\Gateway\Anthropic;

use Generator;
use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Contracts\Gateway\Gateway;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Contracts\Providers\AudioProvider;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\ImageProvider;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Contracts\Providers\TranscriptionProvider;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\ParsesServerSentEvents;
use Ugarit\Ai\Gateway\StepContext;
use Ugarit\Ai\Gateway\StepResponse;
use Ugarit\Ai\Gateway\TextGenerationOptions;
use Ugarit\Ai\Responses\AudioResponse;
use Ugarit\Ai\Responses\EmbeddingsResponse;
use Ugarit\Ai\Responses\ImageResponse;
use Ugarit\Ai\Responses\TranscriptionResponse;
use LogicException;

class AnthropicGateway implements Gateway, StepTextGateway
{
    use Concerns\BuildsTextRequests;
    use Concerns\CreatesAnthropicClient;
    use Concerns\HandlesTextStreaming;
    use Concerns\MapsAttachments;
    use Concerns\MapsMessages;
    use Concerns\MapsTools;
    use Concerns\ParsesTextResponses;
    use HandlesFailoverErrors;
    use ParsesServerSentEvents;

    public function __construct(protected Dispatcher $events) {}

    /**
     * {@inheritdoc}
     */
    public function generateTextStep(
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages,
        array $tools,
        ?array $schema,
        ?TextGenerationOptions $options,
        ?int $timeout,
        StepContext $stepContext,
    ): StepResponse {
        $body = $this->buildTextRequestBody(
            $provider,
            $model,
            $instructions,
            $messages,
            $tools,
            $schema,
            $options,
        );

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)->post('messages', $body),
        );

        $data = $response->json();

        $this->validateTextResponse($data);

        return $this->parseTextResponse($data, $provider, filled($schema))->withRawResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function generateStreamStep(
        string $invocationId,
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages,
        array $tools,
        ?array $schema,
        ?TextGenerationOptions $options,
        ?int $timeout,
        StepContext $stepContext,
    ): Generator {
        $body = $this->buildTextRequestBody(
            $provider,
            $model,
            $instructions,
            $messages,
            $tools,
            $schema,
            $options,
        );

        $body['stream'] = true;

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)
                ->withOptions(['stream' => true])
                ->post('messages', $body),
        );

        return yield from $this->processTextStream(
            $invocationId,
            $provider,
            $model,
            $response->getBody(),
        );
    }

    /**
     * Generate an image.
     *
     * @throws LogicException
     */
    public function generateImage(
        ImageProvider $provider,
        string $model,
        string $prompt,
        array $attachments = [],
        ?string $size = null,
        ?string $quality = null,
        ?int $timeout = null,
    ): ImageResponse {
        throw new LogicException('Anthropic does not support image generation.');
    }

    /**
     * Generate audio from the given text.
     *
     * @throws LogicException
     */
    public function generateAudio(
        AudioProvider $provider,
        string $model,
        string $text,
        string $voice,
        ?string $instructions = null,
        int $timeout = 30,
    ): AudioResponse {
        throw new LogicException('Anthropic does not support audio generation.');
    }

    /**
     * Generate text from the given audio.
     *
     * @throws LogicException
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
        throw new LogicException('Anthropic does not support transcription generation.');
    }

    /**
     * Generate embeddings for the given inputs.
     *
     * @throws LogicException
     */
    public function generateEmbeddings(
        EmbeddingProvider $provider,
        string $model,
        array $inputs,
        int $dimensions,
        int $timeout = 30,
        array $providerOptions = [],
    ): EmbeddingsResponse {
        throw new LogicException('Anthropic does not support embedding generation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function overloadedStatusCodes(): array
    {
        // 529 is Anthropic's own "overloaded" status, plus the shared transient gateway and Cloudflare codes.
        return [529, 502, 503, 504, 520, 522, 524];
    }

    /**
     * {@inheritdoc}
     */
    protected function insufficientCreditPatterns(): array
    {
        return [
            'credit balance',
            'insufficient',
            'quota exceeded',
            'exceeded your current quota',
            'billing',
            'usage limit',
        ];
    }
}
