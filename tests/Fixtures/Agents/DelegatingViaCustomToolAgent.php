<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\AgentCallingTool;

class DelegatingViaCustomToolAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You delegate research using your tool.';
    }

    public function tools(): iterable
    {
        return [
            new AgentCallingTool,
        ];
    }
}
