<?php

namespace Ugarit\Ai\Contracts\Providers;

use Ugarit\Ai\Contracts\Gateway\EmbeddingGateway;
use Ugarit\Ai\Files\Audio;
use Ugarit\Ai\Files\Document;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Files\Video;
use Ugarit\Ai\Responses\EmbeddingsResponse;

interface EmbeddingProvider extends Provider
{
    /**
     * Get embedding vectors representing the given inputs.
     *
     * @param  array<int, string|Audio|Document|Image|Video>  $inputs
     * @param  array<string, mixed>  $providerOptions
     */
    public function embeddings(array $inputs, ?int $dimensions = null, ?string $model = null, int $timeout = 30, array $providerOptions = []): EmbeddingsResponse;

    /**
     * Get the provider's embedding gateway.
     */
    public function embeddingGateway(): EmbeddingGateway;

    /**
     * Set the provider's embedding gateway.
     */
    public function useEmbeddingGateway(EmbeddingGateway $gateway): self;

    /**
     * Get the name of the default embeddings model.
     */
    public function defaultEmbeddingsModel(): string;

    /**
     * Get the default dimensions of the default embeddings model.
     */
    public function defaultEmbeddingsDimensions(): int;
}
