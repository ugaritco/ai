<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Messages\AssistantMessage;
use Ugarit\Ai\Messages\ToolResultMessage;
use Ugarit\Ai\Messages\UserMessage;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\Responses\Data\ToolCall;
use Ugarit\Ai\Responses\Data\ToolResult;

class HistoricalToolCallWithEmptyReasoningAgent implements Agent, Conversational
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
            new UserMessage('check stock'),
            new AssistantMessage(
                'Found the products.',
                collect([
                    new ToolCall('call_empty', 'SearchProducts', ['keyword' => 'lampu'], 'call_empty'),
                ]),
                ['reasoning_content' => '']
            ),
            new ToolResultMessage(collect([
                new ToolResult('call_empty', 'SearchProducts', ['keyword' => 'lampu'], '[{"name":"Lampu LED"}]', 'call_empty'),
            ])),
            new UserMessage('tell me more'),
        ];
    }
}
