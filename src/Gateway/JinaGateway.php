<?php

namespace Ugarit\Ai\Gateway;

use Heritage\Http\Client\PendingRequest;
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

class JinaGateway implements EmbeddingGateway, RerankingGateway
{
    use Concerns\CreatesClient;
    use HandlesFailoverErrors;

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
            fn () => $this->client($provider, $timeout)->post('/embeddings', array_merge(
                ['task' => 'retrieval.passage'],
                $providerOptions,
                [
                    'model' => $model,
                    'input' => array_map(fn (string $text): array => ['text' => $text], $inputs),
                    'dimensions' => $dimensions,
                ],
            )),
        );

        $data = $response->json();

        $embeddings = (new Collection($data['data']))->pluck('embedding')->all();

        return new EmbeddingsResponse(
            $embeddings,
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
     * Get an HTTP client for the Jina API.
     */
    protected function client(EmbeddingProvider|RerankingProvider $provider, int $timeout = 30): PendingRequest
    {
        return $this->createClient(
            $this->baseUrl($provider),
            [
                'Authorization' => 'Bearer '.$provider->providerCredentials()['key'],
                'Content-Type' => 'application/json',
            ],
            $provider->additionalConfiguration()['headers'] ?? [],
            $timeout,
        );
    }

    /**
     * Get the base URL for the Jina API.
     */
    protected function baseUrl(EmbeddingProvider|RerankingProvider $provider): string
    {
        return rtrim($provider->additionalConfiguration()['url'] ?? 'https://api.jina.ai/v1', '/');
    }
}
