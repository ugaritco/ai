<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\NamedTool;

class NamedToolAgent implements Agent, HasTools
{
    use Promptable;

    public function __construct(public readonly string $toolName = 'custom_named_tool') {}

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function tools(): iterable
    {
        return [
            new NamedTool($this->toolName),
        ];
    }
}
