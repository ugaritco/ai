<?php

namespace Tests\Fixtures\Mcp;

use Generator;
use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Mcp\Request;
use Ugarit\Mcp\Response;
use Ugarit\Mcp\Server\Tool;

class FakeStreamingMcpServerTool extends Tool
{
    /**
     * @return Generator<int, Response>
     */
    public function handle(Request $request): Generator
    {
        yield Response::notification('processing/progress', ['step' => 1]);
        yield Response::text('First. ');
        yield 'Second. ';
        yield Response::make([Response::text('Third.')]);
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
