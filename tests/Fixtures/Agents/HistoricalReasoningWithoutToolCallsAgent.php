<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Messages\AssistantMessage;
use Ugarit\Ai\Messages\UserMessage;
use Ugarit\Ai\Promptable;

class HistoricalReasoningWithoutToolCallsAgent implements Agent, Conversational
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function provider(): string
    {
        return 'deepseek';
    }

    public function messages(): iterable
    {
        return [
            new UserMessage('What is 4+4?'),
            new AssistantMessage(
                'The answer is 8.',
                providerContentBlocks: ['reasoning_content' => 'Let me think... 4+4 = 8.']
            ),
        ];
    }
}
