<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Files\Audio;
use Ugarit\Ai\Files\Document;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Files\Video;
use Ugarit\Ai\Responses\EmbeddingsResponse;

interface EmbeddingGateway
{
    /**
     * Generate embedding vectors representing the given inputs.
     *
     * @param  array<int, string|Audio|Document|Image|Video>  $inputs
     * @param  array<string, mixed>  $providerOptions
     */
    public function generateEmbeddings(EmbeddingProvider $provider, string $model, array $inputs, int $dimensions, int $timeout = 30, array $providerOptions = []): EmbeddingsResponse;
}
