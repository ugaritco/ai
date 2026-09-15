<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Concerns\RemembersConversations;

class RememberingAssistantAgent extends AssistantAgent
{
    use RemembersConversations;
}
