<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\AgentPrompt;

class PromptingAgent
{
    public function __construct(
        public string $invocationId,
        public AgentPrompt $prompt,
    ) {}
}
