<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\CanActAsTool;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;

class MiddleManagerAgent implements Agent, CanActAsTool, HasTools
{
    use Promptable;

    public function name(): string
    {
        return 'middle_manager';
    }

    public function description(): string
    {
        return 'Delegate specialized research tasks to the research agent.';
    }

    public function instructions(): string
    {
        return 'You are a middle manager that breaks down tasks and delegates to the research_agent sub-agent.';
    }

    public function tools(): iterable
    {
        return [
            new ResearchAgent,
        ];
    }
}
