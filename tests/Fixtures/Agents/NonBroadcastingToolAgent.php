<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\WithoutBroadcasting;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\Streaming\Events\ToolCall;
use Ugarit\Ai\Streaming\Events\ToolResult;

#[WithoutBroadcasting(ToolResult::class, ToolCall::class)]
class NonBroadcastingToolAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }
}
