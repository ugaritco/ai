<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\WithoutBroadcasting;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\Streaming\Events\TextDelta;

#[WithoutBroadcasting(TextDelta::class)]
class NonBroadcastingTextAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }
}
