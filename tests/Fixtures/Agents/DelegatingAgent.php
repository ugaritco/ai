<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;

class DelegatingAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a project manager that delegates research tasks to your research_agent sub-agent.';
    }

    public function tools(): iterable
    {
        return [
            new ResearchAgent,
        ];
    }
}
