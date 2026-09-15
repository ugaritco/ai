<?php

namespace Ugarit\Ai\Gateway\AzureOpenAi;

use Heritage\Contracts\Events\Dispatcher;
use Heritage\JsonSchema\JsonSchemaTypeFactory;
use Ugarit\Ai\Contracts\Gateway\EmbeddingGateway;
use Ugarit\Ai\Contracts\Gateway\ImageGateway;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\ImageProvider;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Gateway\AzureOpenAi\Concerns\CreatesAzureOpenAiClient;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\ParsesServerSentEvents;
use Ugarit\Ai\Gateway\OpenAi\Concerns\BuildsTextRequests;
use Ugarit\Ai\Gateway\OpenAi\Concerns\HandlesTextGeneration;
use Ugarit\Ai\Gateway\OpenAi\Concerns\HandlesTextSteps;
use Ugarit\Ai\Gateway\OpenAi\Concerns\MapsAttachments;
use Ugarit\Ai\Gateway\OpenAi\Concerns\MapsMessages;
use Ugarit\Ai\Gateway\OpenAi\Concerns\MapsTools;
use Ugarit\Ai\Gateway\OpenAi\Concerns\ParsesTextResponses;
use Ugarit\Ai\ObjectSchema;
use Ugarit\Ai\Responses\Data\GeneratedImage;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\Usage;
use Ugarit\Ai\Responses\EmbeddingsResponse;
use Ugarit\Ai\Responses\ImageResponse;
use Ugarit\Ai\Tools\ToolNameResolver;
use LogicException;

class AzureOpenAiGateway implements EmbeddingGateway, ImageGateway, StepTextGateway
{
    use BuildsTextRequests;
    use CreatesAzureOpenAiClient;
    use HandlesFailoverErrors;
    use HandlesTextGeneration;
    use HandlesTextSteps;
    use MapsAttachments;
    use MapsMessages;
    use MapsTools;
    use ParsesServerSentEvents;
    use ParsesTextResponses;

    public function __construct(protected Dispatcher $events) {}

    public function generateEmbeddings(
        EmbeddingProvider $provider,
        string $model,
        array $inputs,
        int $dimensions,
        int $timeout = 30,
        array $providerOptions = [],
    ): EmbeddingsResponse {
        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)->post('embeddings', array_merge($providerOptions, [
                'model' => $model,
                'input' => $inputs,
                'dimensions' => $dimensions,
            ])),
        );

        $data = $response->json();

        return new EmbeddingsResponse(
            collect($data['data'] ?? [])->pluck('embedding')->all(),
            $data['usage']['prompt_tokens'] ?? 0,
            new Meta($provider->name(), $model),
        );
    }

    /**
     * Generate an image.
     *
     * @param  array<Image>  $attachments
     * @param  '3:2'|'2:3'|'1:1'|null  $size
     * @param  'low'|'medium'|'high'|null  $quality
     *
     * @throws LogicException if attachments are passed; Azure OpenAI does not support image edits.
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
        if (filled($attachments)) {
            throw new LogicException('Azure OpenAI does not support image editing.');
        }

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout ?? 120)->post('images/generations', [
                'model' => $model,
                'prompt' => $prompt,
                'moderation' => 'low',
                ...$provider->defaultImageOptions($size, $quality),
            ]),
        );

        $data = $response->json();

        return new ImageResponse(
            collect($data['data'] ?? [])->map(fn (array $image): GeneratedImage => new GeneratedImage(
                $image['b64_json'] ?? '',
                'image/png',
            )),
            $this->extractImageUsage($data),
            new Meta($provider->name(), $model),
        );
    }

    protected function extractImageUsage(array $data): Usage
    {
        $usage = $data['usage'] ?? [];
        $inputTokens = $usage['input_tokens'] ?? 0;
        $cachedTokens = $usage['input_tokens_details']['cached_tokens'] ?? 0;

        return new Usage(
            promptTokens: $inputTokens - $cachedTokens,
            completionTokens: $usage['output_tokens'] ?? 0,
            cacheReadInputTokens: $cachedTokens,
        );
    }

    /**
     * Map a regular tool to an Azure OpenAI function definition.
     */
    protected function mapTool(Tool $tool, bool $defer = false): array
    {
        $schema = $tool->schema(new JsonSchemaTypeFactory);

        $schemaArray = filled($schema)
            ? (new ObjectSchema($schema))->toSchema()
            : [];

        $definition = array_filter([
            'type' => 'function',
            'name' => ToolNameResolver::resolve($tool),
            'description' => (string) $tool->description(),
            'parameters' => filled($schemaArray) ? [
                'type' => 'object',
                'properties' => $schemaArray['properties'] ?? (object) [],
                'required' => $schemaArray['required'] ?? [],
            ] : null,
        ]);

        if ($defer) {
            $definition['defer_loading'] = true;
        }

        return $definition;
    }
}
