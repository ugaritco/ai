<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Attributes\Model;
use Ugarit\Ai\Attributes\Provider;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Ugarit\Ai\Providers\Tools\ToolSearch;
use Tests\Fixtures\Tools\DeferredTool;
use Tests\Fixtures\Tools\NonStrictTool;

#[Provider('openai')]
#[Model('gpt-5.4')]
class OpenAiToolSearchAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function tools(): iterable
    {
        return [new NonStrictTool, new ToolSearch(tools: [new DeferredTool])];
    }
}
