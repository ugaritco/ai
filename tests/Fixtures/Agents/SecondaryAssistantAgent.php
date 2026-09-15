<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;

class SecondaryAssistantAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant that responds extremely concisely to all queries.';
    }
}
