<?php

namespace Tests\Fixtures\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;
use Tests\Fixtures\Agents\ResearchAgent;

class AgentCallingTool implements Tool
{
    public function description(): string
    {
        return 'Delegates to a research agent from within the tool handler.';
    }

    public function handle(Request $request): string
    {
        return ResearchAgent::make()->prompt('Research Ugarit')->text;
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
