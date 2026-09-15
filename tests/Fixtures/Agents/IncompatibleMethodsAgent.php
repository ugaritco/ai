<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\MaxSteps;
use Ugarit\Ai\Attributes\MaxTokens;
use Ugarit\Ai\Attributes\Temperature;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Promptable;

#[MaxSteps(8)]
#[MaxTokens(1024)]
#[Temperature(0.9)]
class IncompatibleMethodsAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function maxSteps(int $scale): int
    {
        return $scale * 2;
    }

    protected function maxTokens(): int
    {
        return 256;
    }
}
