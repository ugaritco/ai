<?php

namespace Tests\Fixtures\Agents;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Attributes\CacheToolDefinitions;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasStructuredOutput;
use Ugarit\Ai\Promptable;

#[CacheToolDefinitions]
class PromptCacheStructuredAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant that uses structured output.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'symbol' => $schema->string()->required(),
        ];
    }
}
