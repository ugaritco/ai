<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Messages\Message;
use Ugarit\Ai\Promptable;

class ConversationalAgent implements Agent, Conversational
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are a helpful assistant that responds extremely concisely to all queries.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [
            new Message(role: 'user', content: 'My name is Taylor Otwell'),
        ];
    }
}
