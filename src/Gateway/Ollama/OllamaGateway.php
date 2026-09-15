<?php

namespace Ugarit\Ai\Gateway\Ollama;

use Generator;
use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Gateway\EmbeddingGateway;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Exceptions\AiException;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\StepContext;
use Ugarit\Ai\Gateway\StepResponse;
use Ugarit\Ai\Gateway\TextGenerationOptions;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\EmbeddingsResponse;

class OllamaGateway implements EmbeddingGateway, StepTextGateway
{
    use Concerns\BuildsTextRequests;
    use Concerns\CreatesOllamaClient;
    use Concerns\HandlesTextStreaming;
    use Concerns\MapsAttachments;
    use Concerns\MapsMessages;
    use Concerns\MapsTools;
    use Concerns\ParsesTextResponses;
    use HandlesFailoverErrors;

    public function __construct(protected Dispatcher $events)
    {
        //
    }

    /**
     * Generate text for a single Ollama Chat API step.
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
        $body = $this->buildStepBody($provider, $model, $instructions, $messages, $tools, $schema, $options, $stepContext);

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)->post('api/chat', $body),
        );

        $data = $response->json();

        $this->validateTextResponse($data);

        return $this->parseTextResponse($data, $provider, filled($schema))->withRawResponse($response);
    }

    /**
     * Stream text for a single Ollama Chat API step.
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
        $body = $this->buildStepBody($provider, $model, $instructions, $messages, $tools, $schema, $options, $stepContext);
        $body['stream'] = true;

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)
                ->withOptions(['stream' => true])
                ->post('api/chat', $body),
        );

        return yield from $this->processTextStream($invocationId, $provider, $model, $response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    public function generateEmbeddings(
        EmbeddingProvider $provider,
        string $model,
        array $inputs,
        int $dimensions,
        int $timeout = 30,
        array $providerOptions = [],
    ): EmbeddingsResponse {
        $body = array_merge($providerOptions, array_filter([
            'model' => $model,
            'input' => $inputs,
            'dimensions' => $dimensions ?: null,
        ]));

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)->post('api/embed', $body),
        );

        $data = $response->json();

        if (! $data || isset($data['error'])) {
            throw new AiException(sprintf(
                'Ollama Error: %s',
                $data['error'] ?? 'Unknown Ollama error.',
            ));
        }

        return new EmbeddingsResponse(
            $data['embeddings'] ?? [],
            $data['prompt_eval_count'] ?? 0,
            new Meta($provider->name(), $model),
        );
    }
}
