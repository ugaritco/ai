<?php

namespace Tests\Fixtures\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;

class NonStrictTool implements Tool
{
    public function description(): string
    {
        return 'A tool without the Strict attribute.';
    }

    public function handle(Request $request): string
    {
        return 'ok';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer(),
        ];
    }
}
