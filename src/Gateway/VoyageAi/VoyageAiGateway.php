<?php

namespace Ugarit\Ai\Gateway\VoyageAi;

use Heritage\Support\Collection;
use Ugarit\Ai\Contracts\Gateway\EmbeddingGateway;
use Ugarit\Ai\Contracts\Gateway\RerankingGateway;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\RerankingProvider;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\RankedDocument;
use Ugarit\Ai\Responses\EmbeddingsResponse;
use Ugarit\Ai\Responses\RerankingResponse;

class VoyageAiGateway implements EmbeddingGateway, RerankingGateway
{
    use Concerns\CreatesVoyageAiClient;
    use Concerns\MapsEmbeddingInputs;
    use HandlesFailoverErrors;

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
        if ($this->usesMultimodalEmbeddingEndpoint($model, $inputs)) {
            return $this->generateMultimodalEmbeddings($provider, $model, $inputs, $dimensions, $timeout, $providerOptions);
        }

        $data = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout)->post('/embeddings', array_merge($providerOptions, [
                'model' => $model,
                'input' => $inputs,
                'output_dimension' => $dimensions,
            ])),
        )->json();

        return new EmbeddingsResponse(
            (new Collection($data['data']))->pluck('embedding')->all(),
            $data['usage']['total_tokens'] ?? 0,
            new Meta($provider->name(), $model),
        );
    }

    /**
     * Rerank the given documents based on their relevance to the query.
     *
     * @param  array<int, string>  $documents
     */
    public function rerank(
        RerankingProvider $provider,
        string $model,
        array $documents,
        string $query,
        ?int $limit = null
    ): RerankingResponse {
        $data = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)->post('/rerank', array_filter([
                'model' => $model,
                'query' => $query,
                'documents' => $documents,
                'top_k' => $limit,
            ])),
        )->json();

        return new RerankingResponse(
            collect($data['data'])->map(fn (array $result): RankedDocument => new RankedDocument(
                index: $result['index'],
                document: $documents[$result['index']],
                score: $result['relevance_score'],
            ))->all(),
            new Meta($provider->name(), $model),
        );
    }
}
