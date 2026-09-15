<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\ToolChoice;
use Tests\Fixtures\Tools\RandomNumberGenerator;

#[ToolChoice(ToolChoice::required)]
class AttributeToolChoiceAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function tools(): iterable
    {
        return [
            new RandomNumberGenerator,
        ];
    }
}
