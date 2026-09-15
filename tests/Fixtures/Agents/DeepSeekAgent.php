<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\Provider;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;

#[Provider('deepseek')]
class DeepSeekAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }
}
