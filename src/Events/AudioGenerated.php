<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\AudioPrompt;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\AudioResponse;

class AudioGenerated
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public AudioPrompt $prompt,
        public AudioResponse $response,
    ) {}
}
