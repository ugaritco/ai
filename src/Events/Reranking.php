<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\RerankingPrompt;
use Ugarit\Ai\Providers\Provider;

class Reranking
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public RerankingPrompt $prompt,
    ) {}
}
