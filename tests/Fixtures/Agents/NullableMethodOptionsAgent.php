<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\MaxSteps;
use Ugarit\Ai\Attributes\MaxTokens;
use Ugarit\Ai\Attributes\Temperature;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;

#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
class NullableMethodOptionsAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function maxSteps(): ?int
    {
        return null;
    }

    public function maxTokens(): ?int
    {
        return null;
    }

    public function temperature(): ?float
    {
        return null;
    }
}
