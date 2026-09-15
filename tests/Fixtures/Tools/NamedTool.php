<?php

namespace Tests\Fixtures\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;

class NamedTool implements Tool
{
    public function __construct(public readonly string $toolName = 'custom_named_tool') {}

    public function name(): string
    {
        return $this->toolName;
    }

    public function description(): string
    {
        return 'A tool that declares its own name.';
    }

    public function handle(Request $request): string
    {
        return 'ok';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
