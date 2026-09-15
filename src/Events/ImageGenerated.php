<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\ImagePrompt;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\ImageResponse;

class ImageGenerated
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $model,
        public ImagePrompt $prompt,
        public ImageResponse $response,
    ) {}
}
