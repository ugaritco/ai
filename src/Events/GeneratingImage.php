<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\ImagePrompt;
use Ugarit\Ai\Providers\Provider;

class GeneratingImage
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public ImagePrompt $prompt,
    ) {}
}
