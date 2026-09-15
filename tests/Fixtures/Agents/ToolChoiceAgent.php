<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\ToolChoice;
use Tests\Fixtures\Tools\NamedTool;
use Tests\Fixtures\Tools\RandomNumberGenerator;

class ToolChoiceAgent implements Agent, HasTools
{
    use Promptable;

    public function __construct(
        private ToolChoice|string|array|null $toolChoice = null,
    ) {}

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function toolChoice(): ToolChoice|string|array|null
    {
        return $this->toolChoice;
    }

    public function tools(): iterable
    {
        return [
            new RandomNumberGenerator,
            new NamedTool('custom_named_tool'),
        ];
    }
}
