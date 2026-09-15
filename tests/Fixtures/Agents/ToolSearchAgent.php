<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\Providers\Tools\ToolSearch;
use Tests\Fixtures\Tools\FixedNumberGenerator;
use Tests\Fixtures\Tools\SecretCodeGenerator;

class ToolSearchAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are an assistant with access to tools. Some tools are not loaded '
            .'upfront and must be discovered using your tool search capability before '
            .'they can be called. Always use the appropriate tool to answer; never guess. '
            .'When asked for the secret authorization code, find and call the tool that returns it.';
    }

    public function tools(): iterable
    {
        return [
            new FixedNumberGenerator,
            new ToolSearch(tools: [new SecretCodeGenerator]),
        ];
    }
}
