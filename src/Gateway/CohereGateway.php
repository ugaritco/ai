<?php

namespace Ugarit\Ai\Gateway;

use Heritage\Http\Client\PendingRequest;
use Heritage\Support\Collection;
use Ugarit\Ai\Contracts\Gateway\EmbeddingGateway;
use Ugarit\Ai\Contracts\Gateway\RerankingGateway;
use Ugarit\Ai\Contracts\Providers\EmbeddingProvider;
use Ugarit\Ai\Contracts\Providers\RerankingProvider;
use Ugarit\Ai\Gateway\Cohere\Concerns\ParsesEmbeddings;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\RankedDocument;
use Ugarit\Ai\Responses\EmbeddingsResponse;
use Ugarit\Ai\Responses\RerankingResponse;

class CohereGateway implements EmbeddingGateway, RerankingGateway
{
    use Concerns\CreatesClient;
    use HandlesFailoverErrors;
    use ParsesEmbeddings;

    /**
     * Generate embedding vectors representing the given inputs.
     *
     * @param  string[]  $inputs
     * @param  array<string, mixed>  $providerOptions
     */
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
            fn () => $this->client($provider, $timeout)->post('/embed', array_merge(
                [
                    'input_type' => 'search_document',
                    'embedding_types' => ['float'],
                ],
                $providerOptions,
                [
                    'model' => $model,
                    'texts' => $inputs,
                ],
            )),
        );

        $data = $response->json();

        return new EmbeddingsResponse(
            $this->parseCohereEmbeddings($data['embeddings'] ?? []),
            $data['meta']['billed_units']['input_tokens'] ?? 0,
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
        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider)->post('/rerank', array_filter([
                'model' => $model,
                'query' => $query,
                'documents' => $documents,
                'top_n' => $limit,
            ])),
        );

        $data = $response->json();

        $results = (new Collection($data['results']))->map(fn (array $result): RankedDocument => new RankedDocument(
            index: $result['index'],
            document: $documents[$result['index']],
            score: $result['relevance_score'],
        ))->all();

        return new RerankingResponse(
            $results,
            new Meta($provider->name(), $model),
        );
    }

    /**
     * Get an HTTP client for the Cohere API.
     */
    protected function client(EmbeddingProvider|RerankingProvider $provider, int $timeout = 30): PendingRequest
    {
        $config = $provider->additionalConfiguration();

        return $this->createClient(
            $config['url'] ?? 'https://api.cohere.com/v2',
            [
                'Authorization' => 'Bearer '.$provider->providerCredentials()['key'],
                'Content-Type' => 'application/json',
            ],
            $config['headers'] ?? [],
            $timeout,
        );
    }
}
