<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\CanActAsTool;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;

class OrchestratorAgent implements Agent, CanActAsTool, HasTools
{
    use Promptable;

    public function name(): string
    {
        return 'orchestrator';
    }

    public function description(): string
    {
        return 'Orchestrate complex tasks by delegating to specialized sub-agents.';
    }

    public function instructions(): string
    {
        return 'You are an orchestrator that breaks down complex tasks and delegates to the middle_manager sub-agent.';
    }

    public function tools(): iterable
    {
        return [
            new MiddleManagerAgent,
        ];
    }
}
