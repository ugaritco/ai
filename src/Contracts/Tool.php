<?php

namespace Ugarit\Ai\Contracts;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Ai\Tools\Request;
use Stringable;

interface Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string;

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string;

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array;
}
