<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\ToolChoice;
use Tests\Fixtures\Tools\RandomNumberGenerator;

#[ToolChoice(ToolChoice::required)]
class ThinkingToolChoiceAgent implements Agent, HasProviderOptions, HasTools
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

    public function providerOptions(Lab|string $provider): array
    {
        return [
            'thinking' => [
                'type' => 'enabled',
                'budget_tokens' => 10_000,
            ],
        ];
    }
}
