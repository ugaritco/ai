<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Prompts\AgentPrompt;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\StreamedAgentResponse;

class AgentPrompted
{
    public function __construct(
        public string $invocationId,
        public AgentPrompt $prompt,
        public StreamedAgentResponse|AgentResponse $response
    ) {}
}
