<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Concerns\RemembersConversations;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\ApprovableNumberGenerator;

class RememberingApprovableAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are a helpful assistant that always uses the available tool to generate a number.';
    }

    /**
     * Get the tools available to the agent.
     */
    public function tools(): iterable
    {
        return [new ApprovableNumberGenerator];
    }
}
