<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\MaxSteps;
use Ugarit\Ai\Attributes\MaxTokens;
use Ugarit\Ai\Attributes\Provider;
use Ugarit\Ai\Attributes\Temperature;
use Ugarit\Ai\Attributes\TopP;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;

#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
#[TopP(0.8)]
#[Provider('anthropic')]
class AttributeAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }
}
