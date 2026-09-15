<?php

namespace Tests\Fixtures\Mcp;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Mcp\Request;
use Ugarit\Mcp\Response;
use Ugarit\Mcp\Server\Tool;

class FakeArrayMcpServerTool extends Tool
{
    /**
     * @return array<int, Response|string>
     */
    public function handle(Request $request): array
    {
        return [
            Response::text('First. '),
            'Second. ',
            Response::text('Third.'),
        ];
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
