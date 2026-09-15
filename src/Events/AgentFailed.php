<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\AgentPrompt;
use Throwable;

class AgentFailed
{
    public function __construct(
        public string $invocationId,
        public AgentPrompt $prompt,
        public Throwable $exception,
    ) {}
}
