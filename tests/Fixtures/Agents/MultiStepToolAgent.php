<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\MaxSteps;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\FixedNumberGenerator;

#[MaxSteps(10)]
class MultiStepToolAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant that generates numbers.';
    }

    public function tools(): iterable
    {
        return [new FixedNumberGenerator];
    }
}
