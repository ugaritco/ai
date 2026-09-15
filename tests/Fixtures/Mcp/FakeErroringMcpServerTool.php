<?php

namespace Tests\Fixtures\Mcp;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Mcp\Request;
use Ugarit\Mcp\Response;
use Ugarit\Mcp\Server\Tool;

class FakeErroringMcpServerTool extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::error('Something went wrong.');
    }

    /**
     * @return array<string, Type>
     */
    #[\Override]
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
