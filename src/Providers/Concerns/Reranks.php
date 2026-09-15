<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Events\Reranked;
use Ugarit\Ai\Events\Reranking;
use Ugarit\Ai\Prompts\RerankingPrompt;
use Ugarit\Ai\Responses\RerankingResponse;

trait Reranks
{
    /**
     * Rerank the given documents based on their relevance to the query.
     *
     * @param  array<int, string>  $documents
     */
    public function rerank(array $documents, string $query, ?int $limit = null, ?string $model = null): RerankingResponse
    {
        $invocationId = (string) Str::uuid7();

        $model ??= $this->defaultRerankingModel();

        $prompt = new RerankingPrompt($documents, $query, $limit, $this, $model);

        if (Ai::rerankingIsFaked()) {
            Ai::recordReranking($prompt);
        }

        $this->events->dispatch(new Reranking(
            $invocationId, $this, $model, $prompt,
        ));

        return tap($this->rerankingGateway()->rerank(
            $this,
            $model,
            $documents,
            $query,
            $limit
        ), fn (RerankingResponse $response) => $this->events->dispatch(new Reranked(
            $invocationId, $this, $model, $prompt, $response,
        )));
    }
}
