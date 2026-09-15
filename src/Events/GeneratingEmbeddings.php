<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\EmbeddingsPrompt;
use Ugarit\Ai\Providers\Provider;

class GeneratingEmbeddings
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public EmbeddingsPrompt $prompt,
    ) {}
}
