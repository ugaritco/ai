<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Providers\RerankingProvider;
use Ugarit\Ai\Responses\RerankingResponse;

interface RerankingGateway
{
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
    ): RerankingResponse;
}
