<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\RerankingPrompt;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\RerankingResponse;

class Reranked
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public RerankingPrompt $prompt,
        public RerankingResponse $response,
    ) {}
}
