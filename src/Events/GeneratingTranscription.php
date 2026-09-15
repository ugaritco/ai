<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\TranscriptionPrompt;
use Ugarit\Ai\Providers\Provider;

class GeneratingTranscription
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public TranscriptionPrompt $prompt,
    ) {}
}
