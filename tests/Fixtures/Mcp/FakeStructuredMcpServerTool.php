<?php

namespace Tests\Fixtures\Mcp;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Mcp\Request;
use Ugarit\Mcp\Response;
use Ugarit\Mcp\ResponseFactory;
use Ugarit\Mcp\Server\Tool;

class FakeStructuredMcpServerTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        return Response::structured([
            'temperature' => 72,
            'conditions' => 'Sunny',
        ]);
    }

    /**
     * @return array<string, Type>
     */
    #[\Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()->required(),
        ];
    }
}
